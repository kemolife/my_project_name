<?php

namespace SingAppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhotosBusinessType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('image', FileType::class,
            ['attr' =>
                ['placeholder' => 'Photo', 'class' => 'form-control border-input', 'accept' => 'image/*', 'multiple' => 'multiple'],
                'multiple' => true, 'label' => 'Photos', 'data_class' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\BusinessImage'
        ));
    }

}