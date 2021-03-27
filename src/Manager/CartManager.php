<?php

namespace App\Manager;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Storage\CartSessionStorage;
use App\Storage\CartStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class CartManager
{
    /**
     * @var CartSessionStorage
     */
    private $cartStorage;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CartManager constructor.
     *
     * @param CartSessionStorage $cartStorage
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        CartStorageInterface $cartStorage,
        EntityManagerInterface $entityManager
    ) {
        $this->cartStorage = $cartStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * Gets the current cart.
     *
     * @return Order
     */
    public function getCurrentCart(): Order
    {
        $cart = $this->cartStorage->getCart();
        if (!$cart) {
            $cart = $this->cartStorage->createCart();
        }

        return $cart;
    }

    /**
     * Persists the cart in session and database.
     *
     * @param Order $cart
     * @param bool $persist
     */
    public function save(Order $cart, $persist = false): void
    {
        if($persist){
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
        // Persist in session
        $this->cartStorage->save($cart);
    }

    public function clear(){
        $this->cartStorage->clear();
    }


    public function checkPriceChange()
    {
        $productRepository = $this->entityManager->getRepository(Product::class);
        $changedProducts = [];
        /** @var OrderItem $item */
        foreach($this->getCurrentCart()->getItems() as $item){

            /** @var Product $cartProduct */
            $cartProduct = $item->getProduct();
            /** @var Product $product */
            $product = $productRepository->find($cartProduct->getId());
            if(floatval($product->getPrice()) != floatval($cartProduct->getPrice())){
                $item->setProduct($product);
                $changedProducts[]=$product;
            }
        }
        return $changedProducts;
    }

    public function reload()
    {
        $changedProducts = [];
        $productRepository = $this->entityManager->getRepository(Product::class);
        /** @var OrderItem $item */
        $cart = $this->getCurrentCart();
        foreach($cart->getItems() as $item){

            /** @var Product $cartProduct */
            $cartProduct = $item->getProduct();
            /** @var Product $product */
            $product = $productRepository->find($cartProduct->getId());
            if(floatval($product->getPrice()) != floatval($cartProduct->getPrice())){
                $changedProducts[]=$product;
            }
            $item->setProduct($product);
            $item->setPrice($product->getPrice());
            $item->setDiscount($product->getDiscount());
            $item->setTax($product->getTax());
        }

        $cart->setUpdatedAt(new \DateTime());
        return $changedProducts;
    }

}
