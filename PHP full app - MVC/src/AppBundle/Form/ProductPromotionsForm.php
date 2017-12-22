<?php

namespace AppBundle\Form;

use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductPromotionsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("promotion", EntityType::class, [
                "class" => Promotion::class,
                "placeholder" => "Choose promotion",
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->add("product", EntityType::class, [
                "class" => Product::class,
                "label" => "Products",
                "placeholder" => "Choose product",
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getBlockPrefix()
    {
        return 'app_bundle_product_promotions_form';
    }
}
