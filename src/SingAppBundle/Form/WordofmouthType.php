<?php

namespace SingAppBundle\Form;


use SingAppBundle\Entity\WordofmouthAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WordofmouthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userEmail', EmailType::class, ['attr' => ['placeholder' => 'Password', 'class' => 'form-control'], 'label' => 'Word Of Mouth Email'])
            ->add('userPassword', PasswordType::class, ['attr' => ['placeholder' => 'Name', 'class' => 'form-control'], 'label' => 'Word Of Mouth Password'])
            ->add('login', SubmitType::class, ['attr' => ['class' => 'form-control form-button btn-primary'], 'label' => 'Login']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => WordofmouthAccount::class,
        ));
    }
}