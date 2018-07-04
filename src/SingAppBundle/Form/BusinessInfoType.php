<?php

namespace SingAppBundle\Form;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Form\DataTransformers\BusinessImageTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
        add('category', ChoiceType::class,[
            'attr' => ['class' => 'form-control'],
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
        add('additionalCategories', EntityType::class,  [
            'class' => AdditionalCategoriesBusinessInfo::class,
            'multiple' => true,
            'choice_label' => 'name'
        ])->
        add('address', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('phoneNumber', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('website', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('description', TextareaType::class, ['attr' => ['class' => 'form-control']])->
        add('openingHours')->
        add('logo', FileType::class, [
            'data_class' => null, 'attr' => [
            'class' => 'form-control border-input'],
            'required' => false])->
        add('paymentOptions')->
        add('video', TextType::class, ['attr' => ['class' => 'form-control']])->
        add('uploadedFiles', FileType::class, [
            'attr' => [
                'placeholder' => 'Photo',
                'class' => 'form-control border-input',
                'accept' => 'image/*',
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
