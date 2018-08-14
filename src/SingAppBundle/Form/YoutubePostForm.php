<?php

namespace SingAppBundle\Form;

use SingAppBundle\Services\YoutubeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class YoutubePostForm extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['attr' => ['placeholder' => 'Title', 'class' => 'form-control'], 'label' => 'Title', 'required' => true])
            ->add('caption', TextType::class, ['attr' => ['placeholder' => 'Description', 'class' => 'form-control'], 'label' => 'Description', 'required' => true])
            ->add('visibility', ChoiceType::class, [
                'choices' => [
                    'Public' => 'public',
                    'Private' => 'private',
                    'Unlisted' => 'unlisted',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('channelId', ChoiceType::class, [
                'choices' => $options['channels'],
                'attr' => ['class' => 'form-control'],
                'label' => 'Channel',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SingAppBundle\Entity\YoutubePost',
            'allow_extra_fields' => true,
            'channels' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_youtubepost';
    }
}
