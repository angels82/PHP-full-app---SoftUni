<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('category', EntityType::class,[
                'class'=>Category::class,
                'choice_label'=>'name',
                'placeholder'=>'Choose a category'
            ])

            ->add('imageFile', FileType::class)
            ->add('quantity')
            ->add('price')
            ->add("promotions", EntityType::class, [
                "class" => 'AppBundle\Entity\Promotion',
                'placeholder' => 'Choose promotion',
                'required' => false
            ])
            ->add('selling', ChoiceType::class, array(
                'choices'  => array(
                    'Yes' => 'Yes',
                    'No' => 'No',
                ), 'placeholder'=>'Put for selling'
            ))
        ->add('submit', SubmitType::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\product'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_product';
    }


}
