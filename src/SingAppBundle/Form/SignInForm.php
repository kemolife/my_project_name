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

class SignInForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['attr' => ['placeholder' => 'E-mail or username', 'class' => 'form-control'], 'label' => 'E-mail', 'required' => false])
            ->add('password', PasswordType::class, ['attr' => ['placeholder' => 'Password', 'class' => 'form-control'], 'label' => 'Password', 'required' => false])
            ->add('signin', SubmitType::class, ['attr' => ['class' => 'form-control form-button btn-primary', 'data-loading-text' => '<i class="fas fa-spinner fa-spin"></i> Loading'], 'label' => 'Sign in']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_auth';
    }
}
