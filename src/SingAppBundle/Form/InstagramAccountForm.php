<?php

namespace SingAppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InstagramAccountForm extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['attr' => ['placeholder' => 'Name', 'class' => 'form-control'], 'label' => 'Name'])
            ->add('login', TextType::class, ['attr' => ['placeholder' => 'Login', 'class' => 'form-control border-input'], 'label' => 'Login'])
            ->add('password', PasswordType::class,['attr' => ['placeholder' => 'Password', 'class' => 'form-control'], 'label' => 'Password'])
            ->add('loginForm', SubmitType::class, ['attr' => ['class' => 'form-control form-button btn-primary'], 'label' => 'Login']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\InstagramAccount',
            'allow_extra_fields' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_instagram_account';
    }
}
