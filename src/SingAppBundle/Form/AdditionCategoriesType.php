<?php

namespace SingAppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdditionCategoriesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nameCollection', ChoiceType::class,[
            'label' => false,
            'multiple' => true,
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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\AdditionalCategoriesBusinessInfo'
        ));
    }

}