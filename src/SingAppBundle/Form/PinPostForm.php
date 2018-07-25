<?php

namespace SingAppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PinPostForm extends AbstractType
{
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['placeholder' => 'Name', 'class' => 'form-control border-input']])
            ->add('caption', TextareaType::class, ['attr' => ['placeholder' => 'Caption', 'class' => 'form-control border-input']])
            ->add('postDate', DateTimeType::class, array('widget' => 'single_text', 'html5' => false, 'attr' => ['class' => 'js-datepicker form-control border-input']))
            ->add('uploadedFiles', FileType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Photo',
                    'class' => 'form-control border-input',
                    'accept' => 'image/*',
                    'multiple' => 'multiple'],
                'multiple' => true,
                'label' => 'Photos',
                'data_class' => null,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\PinterestPin',
            'allow_extra_fields' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'SingAppBundle_pinterest_pin';
    }
}
