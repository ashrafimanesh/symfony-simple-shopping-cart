<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Util\CartItemFormTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddToCartType extends AbstractType
{
    protected $transformer;

    public function __construct(CartItemFormTransformer $transformer){

        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Product $product */
        $product = $options['data']['product'];
        /** @var Order|null $cart */
        $cart = $options['data']['cart'] ?? null;

        $builder->add('quantity');

        if($cart && ($cart->existItem($product->getId()))){
            $builder->add('add', SubmitType::class, [
                'label' => 'Update quantity',
            ]);
        }
        else{
            $builder->add('add', SubmitType::class, [
                'label' => 'Add to cart'
            ]);
        }
        $builder->addViewTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItem::class
        ]);
    }
}
