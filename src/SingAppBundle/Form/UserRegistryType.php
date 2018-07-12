<?php

namespace SingAppBundle\Form;


use SingAppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['attr' => ['placeholder' => 'Password', 'class' => 'form-control'], 'label' => 'Email'])
            ->add('username', TextType::class, ['attr' => ['placeholder' => 'Name', 'class' => 'form-control'], 'label' => 'Name'])
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Password', 'attr' => [ 'class' => 'form-control']],
                'second_options' => ['label' => 'Repeat Password', 'attr' => ['class' => 'form-control']],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}