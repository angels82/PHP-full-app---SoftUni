<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeUpdate extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('PATCH')
            ->add('name')
            ->add('description')
            ->add('category', EntityType::class,[
                'class'=>Category::class,
                'choice_label'=>'name',
                'placeholder'=>'Choose a category',
                'data_class' => null
            ])
            ->add('imageFile', FileType::class, array('data_class' => null, 'required'=>false))
            ->add('quantity')
            ->add('original_price')
            ->add("promotions", EntityType::class, [
                "class" => 'AppBundle\Entity\Promotion',
                "required" => false,
                'placeholder'=>'Choose promotion'
            ])
        ->add('selling', ChoiceType::class, array(
                'choices'  => array(
                    'Yes' => 'Yes',
                    'No' => 'No',
                ), 'placeholder'=>'Put for selling'
            ));
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
