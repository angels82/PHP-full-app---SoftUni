<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use AppBundle\Entity\Category;
use AppBundle\Entity\Promotion;

class CategoryPromotionsForm extends AbstractType
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
            ->add("category", EntityType::class, [
                "class" => Category::class,
                "label" => "Products category",
                "placeholder" => "Choose category",
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
