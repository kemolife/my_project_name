<?php

namespace SingAppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BusinessInfoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')->
        add('category')->
        add('additionalCategories', TextType::class)->
        add('address')->
        add('phoneNumber')->
        add('website')->
        add('description')->
        add('openingHours')->
        add('logo')->
        add('paymentOptions')->
        add('video')->
        add('photos', TextType::class);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\BusinessInfo'
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
