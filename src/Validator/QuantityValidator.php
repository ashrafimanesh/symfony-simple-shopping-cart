<?php

namespace App\Validator;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class QuantityValidator extends ConstraintValidator
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function validate($value, Constraint $constraint)
    {

        /* @var $constraint \App\Validator\Quantity */

        if (null === $value || '' === $value) {
            return;
        }
        if(!($constraint->product instanceof Product)){
            $this->context->buildViolation('The product is not valid.')
                ->addViolation();
            return;
        }

        $quantity = $constraint->product->getQuantity();
        if(!is_null($quantity) && $quantity<$value){
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ compared_value }}', $quantity)
                ->addViolation();
        }
    }
}
