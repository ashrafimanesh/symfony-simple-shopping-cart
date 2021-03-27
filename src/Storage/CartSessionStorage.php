<?php


namespace App\Storage;


use App\Entity\Order;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartSessionStorage implements CartStorageInterface
{
    /**
     * The session storage.
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    const CART_KEY_NAME = 'shopping_cart';

    /**
     * CartSessionStorage constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Gets the cart in session.
     *
     * @return Order|null
     */
    public function getCart()
    {
        $data = $this->session->get(self::CART_KEY_NAME);
        if(!$data){
            return null;
        }

        return $data;
//
//        return $this->cartRepository->findOneBy([
//            'id' => $this->getCartId(),
//            'owner_id'=>$user->getId(),
//            'status' => Order::STATUS_CART
//        ]);
    }

    public function createCart():Order{
        $cart = new Order();
        $cart
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
        return $cart;
    }

    /**
     * Sets the cart in session.
     *
     * @param Order $cart
     */
    public function save(Order $cart)
    {
        $this->session->set(self::CART_KEY_NAME, $cart);
    }

    public function clear(){
        $this->session->remove(self::CART_KEY_NAME);
    }
}
