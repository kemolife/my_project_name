<?php

namespace SingAppBundle\Form;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Form\DataTransformers\BusinessImageTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessInfoType extends AbstractType
{

    private $transformer;

    public function __construct()
    {
        $this->transformer = new BusinessImageTransformer();
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')->
        add('category', ChoiceType::class,[
            'choices'  => array(
                'Baby' => 'Baby',
                'Beauty and fragrances' => 'Beauty and fragrances',
                'Books and magazines' => 'Books and magazines',
                'Business to business' => 'Business to business',
                'Clothing, accessories, and shoes' => 'Clothing, accessories, and shoes',
                'Computers, accessories, and services' => 'Computers, accessories, and services',
                'Education' => 'Education',
                'Electronics and telecom' => 'Electronics and telecom',
                'Entertainment and media' => 'Entertainment and media'
            ),
        ])->
        add('address')->
        add('phoneNumber')->
        add('website')->
        add('description')->
        add('openingHours')->
        add('logo')->
        add('paymentOptions')->
        add('video')->
        add('photos', CollectionType::class, [
            'entry_type' => PhotosBusinessType::class,
            'label' => false,
            'allow_add'    => true,
            'allow_delete' => true,
            'prototype'    => true,
            'required'     => false,
            'by_reference' => false,
            'data_class' => null
        ]);
        $builder->get('photos')->addModelTransformer($this->transformer);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\BusinessInfo',
            'allow_extra_fields' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'singappbundle_businessinfo';
    }


}
