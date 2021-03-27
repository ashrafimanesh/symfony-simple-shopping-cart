<?php

namespace App\Util;


use App\Entity\OrderItem;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CartItemFormTransformer implements DataTransformerInterface
{

    public function transform($value)
    {
        $orderItem = new OrderItem();
        $orderItem->setProduct($value['product']);
        $orderItem->setQuantity($value['quantity'] ?? 0);
        $orderItem->setOrder($value['order'] ?? null);
        return $orderItem;
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * This method is called when {@link Form::submit()} is called to transform the requests tainted data
     * into an acceptable format.
     *
     * The same transformers are called in the reverse order so the responsibility is to
     * return one of the types that would be expected as input of transform().
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as NULL). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function reverseTransform($value)
    {
        return [
            'quantity'=>$value->getQuantity(),
            'product'=>$value->getProduct(),
            'order'=>$value->getOrder(),
        ];
    }
}