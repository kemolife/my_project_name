<?php

namespace SingAppBundle\Form;

use SingAppBundle\Services\YoutubeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PinterestPostForm extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['placeholder' => 'Title', 'class' => 'form-control'], 'label' => 'Title', 'required' => true])
            ->add('caption', TextType::class, ['attr' => ['placeholder' => 'Description', 'class' => 'form-control'], 'label' => 'Description', 'required' => true])
            ->add('board', ChoiceType::class, [
                'choices' => $options['board'],
                'attr' => ['class' => 'form-control'],
                'label' => 'Board',
            ])
            ->add('link', UrlType::class, ['attr' => ['placeholder' => 'Link', 'class' => 'form-control'], 'label' => 'Link', 'required' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\PinterestPin',
            'allow_extra_fields' => true,
            'board' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_pinterestpost';
    }
}
