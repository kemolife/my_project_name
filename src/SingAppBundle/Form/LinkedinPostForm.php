<?php

namespace SingAppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LinkedinPostForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['placeholder' => 'Title', 'class' => 'form-control'], 'label' => 'Title', 'required' => true])
            ->add('caption', TextType::class, ['attr' => ['placeholder' => 'Description', 'class' => 'form-control'], 'label' => 'Description', 'required' => true])
            ->add('url', UrlType::class, ['attr' => ['placeholder' => 'Url', 'class' => 'form-control'], 'label' => 'Url', 'required' => true])
            ->add('visibility', ChoiceType::class, [
                'choices' => [
                    'Anyone' => 'anyone',
                    'Connections Only' => 'connections-only'
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\LinkedinPost',
            'allow_extra_fields' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_linkedinpost';
    }
}
