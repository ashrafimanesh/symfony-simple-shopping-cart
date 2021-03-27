<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddToCartType;
use App\Manager\CartManager;
use App\Repository\ProductRepository;
use App\Util\CartItemFormTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/{id}", name="product.detail")
     * @param Product $product
     * @param Request $request
     * @param CartManager $cartManager
     * @param CartItemFormTransformer $transformer
     * @return Response
     */
    public function index(Product $product, Request $request, CartManager $cartManager, CartItemFormTransformer $transformer, ValidatorInterface $validator): Response
    {


        $cart = $cartManager->getCurrentCart();
        /** @var Form $form */
        $form = $this->createForm(AddToCartType::class, compact('cart', 'product'));
        $errors = null;
        if ($request->isMethod('POST')) {

            $form->submit($request->request->get($form->getName()));

            $formData = $form->getData();

            $item = $transformer->transform($formData);
            /** @var ConstraintViolationList| null $errors */
            $errors = $validator->validate($item);
            $isValid = count($errors) <= 0;
            if ($form->isSubmitted() && $isValid) {
                $cart
                    ->addItem($item)
                    ->setUpdatedAt(new \DateTime());

                $cartManager->save($cart);

                return $this->redirectToRoute('product.detail', ['id' => $product->getId(), 'errors' => $errors]);
            }
        }
        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'cart' => $cart,
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/search/product", name="product.search")
     *
     */
    public function search(ProductRepository $productRepository, Request $request, CartManager $cartManager)
    {
        if (strlen($request->query->get('q')) < 3) {
            return new Response(json_encode(['entities' => ['error' => "No result"]]));
        }

        $limit = $request->query->get('limit', 5);
        if ($limit > 20) {
            $limit = 20;
        }

        $products = $productRepository->findAllMatching($request->query->get('q'), $limit);
        $result['entities'] = [];
        if (!$products) {
            $result['entities']['error'] = "No result";
        } else {
            /** @var Product $product */
            $cart = $cartManager->getCurrentCart();

            foreach ($products as $product) {
                $cartItem = $cart->existItem($product->getId());
                $cartQuantity = $cartItem && $cartItem->getQuantity() > 0 ? $cartItem->getQuantity() : 0;
                $result['entities'][$product->getId()] = ['name' => $product->getName(), 'cart_quantity' => $cartQuantity];
            }
        }

        return new Response(json_encode($result));
    }
}
