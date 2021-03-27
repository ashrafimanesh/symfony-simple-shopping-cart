<?php

namespace App\Validator;

use App\Entity\Product;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Quantity extends Constraint
{
    /** @var Product */
    public $product;
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'The value "{{ value }}" is not valid. This value should be less than or equal to "{{ compared_value }}".';
}
