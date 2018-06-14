<?php

namespace ReviewsServicesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicesSettingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('facebook_app_id', TextType::class, [
                'label' => 'Facebook Application Id',
                'required' => true])
            ->add('app_secret', TextType::class, [
                'label' => 'App Secret',
                'required' => true])
            ->add('page_id', TextType::class, [
                'label' => 'Page Id',
                'required' => false])
            ->add('token', TextType::class, [
                'label' => 'Token',
                'required' => false])
            ->add('place_id', TextType::class, [
                'label' => 'Place Id',
                'required' => false])
            ->add('app_key', TextType::class, [
                'label' => 'App Key',
                'required' => false])
            ->add('agent_key', TextType::class, [
                'label' => 'Agent key',
                'required' => false])
            ->add('tripadvisor_location_id', TextType::class, [
                'label' => 'Location Id',
                'required' => false])
            ->add('tripadvisor_access_token', TextType::class, [
                'label' => 'Access Token',
                'required' => false])
            ->add('whitecoat_id', TextType::class, [
                'label' => 'Practice Id',
                'required' => false])
            ->add('yelp_business_id', TextType::class, [
                'label' => 'Business Id',
                'required' => false])
            ->add('yelp_access_token', TextType::class, [
                'label' => 'Access Token',
                'required' => false])
            ->add('zomato_business_id', TextType::class, [
                'label' => 'Business Id',
                'required' => false])
            ->add('zomato_access_token', TextType::class, [
                'label' => 'Access Token',
                'required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ReviewsServicesBundle\Entity\SettingData'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'reviewsservicesbundle_servicessetting';
    }


}
