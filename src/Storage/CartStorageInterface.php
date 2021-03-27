<?php
/**
 * Created by PhpStorm.
 * User: ashrafimanesh@gmail.com
 * Date: 3/26/21
 * Time: 10:13 AM
 */

namespace App\Storage;


use App\Entity\Order;

interface CartStorageInterface
{

    public function createCart():Order;

    public function save(Order $cart);

    public function getCart();

    public function clear();
}