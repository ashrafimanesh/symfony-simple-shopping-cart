<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\CartType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use App\Util\CartItemFormTransformer;
use App\Util\OrderCodeGenerator;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(CartManager $cartManager, Request $request, ValidatorInterface $validator, CartItemFormTransformer $transformer): Response
    {
        /*
         * Reload cart items to update product prices
         */
        $cartManager->reload();
        $cart = $cartManager->getCurrentCart();
        $form = $this->createForm(CartType::class, $cart);
        /** @var ConstraintViolationList| null $allErrors */
        $allErrors = null;
        $errors = null;
        if ($request->isMethod('POST')) {

            $allErrors = $this->validateUpdatedItems($request, $validator, $transformer, $form, $cart, $allErrors);

            if(sizeof($allErrors) <= 0){

                $form->submit($request->request->get($form->getName()));

                if($form->isSubmitted() && $form->isValid()){
                    $cartManager->save($cart);
                    return $this->redirectToRoute('cart');
                }

            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
            'errors'=>$allErrors
        ]);
    }


    /**
     * @Route("/cart/checkout", name="cart_checkout")
     */
    public function checkout(CartManager $cartManager, Security $security, OrderCodeGenerator $orderCodeGenerator)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /*
         * Reload cart items to update product prices
         */
        $cartManager->reload();

        $cart = $cartManager->getCurrentCart();
        $cart->setOwner($security->getUser());
        $cart->setTotalPrice($cart->getTotal());
        $cart->setCode($orderCodeGenerator->generate(5));
        $error = null;
        try{
            $cartManager->save($cart, true);
            if($cart->getId()){
                $cartManager->clear();
                return $this->redirectToRoute('cart.detail', ['id'=>$cart->getId()]);
            }
            else{
                $error = "Please try again!";
            }
        }catch (UniqueConstraintViolationException $exception){
            $error = "Please try again!";
        }

        return $this->render('cart/detail.html.twig', [
            'cart' => $cartManager->getCurrentCart(),
            'order'=> $cart,
            'error' => $error
        ]);
    }

    /**
     * @Route("/cart/add", name="cart.add")
     *
     */
    public function add(Request $request, ProductRepository $productRepository, CartManager $cartManager, CartItemFormTransformer $transformer, ValidatorInterface $validator)
    {
        $product = $productRepository->find($request->request->get('product_id'));
        if(!$product){
            return new Response(json_encode(['status'=>false,'message'=>'Invalid Product']));
        }
        $item = $transformer->transform([
            'product'=>$product,
            'quantity'=>$request->request->get('quantity')
        ]);

        /** @var ConstraintViolationList| null $errors */
        $errors = $validator->validate($item);
        $isValid = count($errors) <= 0;
        if($isValid){
            $cart = $cartManager->getCurrentCart();
            $cart
                ->addItem($item)
                ->setUpdatedAt(new \DateTime());

            $cartManager->save($cart);
            return new Response(json_encode(['status'=>true,'message'=>'']));
        }

        return new Response(json_encode(['status'=>false,'message'=>$errors->get(0)->getMessage()]));
    }
    /**
     * @Route("/cart/detail/{id}", name="cart.detail")
     *
     */
    public function detail(Order $order, CartManager $cartManager, Security $security)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $security->getUser();

        if ($order->getOwner() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('cart/detail.html.twig', [
            'cart'=>$cartManager->getCurrentCart(),
            'order' => $order,
            'error' => null
        ]);

    }

    /**
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param CartItemFormTransformer $transformer
     * @param $form
     * @param $cart
     * @param $allErrors
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateUpdatedItems(Request $request, ValidatorInterface $validator, CartItemFormTransformer $transformer, $form, $cart, $allErrors)
    {
        $requestItems = $request->request->get($form->getName())['items'] ?? [];
        foreach ($cart->getItems() as $i => $item) {
            if (isset($requestItems[$i])) {
                $orderItem = $transformer->transform(['product' => $item->getProduct(), 'quantity' => $requestItems[$i]['quantity']]);
                $errors = $validator->validate($orderItem);
                if (!$allErrors) {
                    $allErrors = $errors;
                } else {
                    $allErrors->addAll($errors);
                }
            }
        }
        return $allErrors;
    }
}
