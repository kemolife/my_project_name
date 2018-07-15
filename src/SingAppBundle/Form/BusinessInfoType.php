<?php

namespace SingAppBundle\Form;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Form\DataTransformers\BusinessImageTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        $builder->add('name', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('category', EntityType::class,  [
            'attr' => [
                'class' => 'form-control',
            ],
            'class' => AdditionalCategoriesBusinessInfo::class,
            'choice_label' => 'name'
        ])->
        add('additionalCategories', EntityType::class,  [
            'class' => AdditionalCategoriesBusinessInfo::class,
            'multiple' => true,
            'attr' => [
                'class' => 'additional-categories-select2',
            ],
            'choice_label' => 'name'
        ])->
        add('address', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('email', EmailType::class, ['attr' => ['class' => 'form-control']])->
        add('phoneNumber', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('website', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])->
        add('description', TextareaType::class, ['required' => false, 'attr' => ['class' => 'form-control']])->
//        add('openingHours')->
        add('logo', FileType::class, [
            'attr' => ['class' => 'form-control-file'],
            'data_class' => null,
            'required' => false])->
        add('paymentOptions', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])->
        add('video', TextType::class, ['required' => false, 'attr' => ['class' => 'form-control']])->
        add('uploadedFiles', FileType::class, [
            'attr' => [
                'placeholder' => 'Photo',
                'accept' => 'image/*',
                'class' => 'form-control-file',
                'multiple' => 'multiple'],
            'multiple' => true,
            'label' => 'Photos',
            'required' => false,
            'data_class' => null,
        ]);
//        $builder->get('photos')->addModelTransformer($this->transformer);
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
