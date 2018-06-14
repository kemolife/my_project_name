<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use ReviewsServicesBundle\Entity\ServicesSetting;
use ReviewsServicesBundle\Entity\SettingData;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SettingServices
{
    private $entityManager;
    private $serializer;
    private $data;

    /**
     * SettingServices constructor.
     * @param EntityManagerInterface $entityManager
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer(
            array(new ObjectNormalizer(new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader())))),
            array(new JsonEncoder())
        );
    }

    /**
     * @param SettingData $model
     * @throws \Exception
     */
    public function create(SettingData $model)
    {
        try {
            $this->entityManager->persist($this->prepareSetting($model));
            $this->entityManager->flush();
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function update(SettingData $settingData, ServicesSetting $model)
    {
        var_dump($settingData);
        try {
            $this->prepareSetting($settingData, $model);
            $this->entityManager->flush();
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    private function prepareSetting(SettingData $data, $model = null): ServicesSetting
    {
        $this->normalizeData($data);

        $serviceSetting = $model??new ServicesSetting();
        $serviceSetting->setFacebookS($this->serializer->serialize($this->data['facebook'], 'json'));
        $serviceSetting->setGoogleS($this->serializer->serialize($this->data['google'], 'json'));
        $serviceSetting->setRatemyagentS($this->serializer->serialize($this->data['ratemyagent'], 'json'));
        $serviceSetting->setTripadvisorS($this->serializer->serialize($this->data['tripadvisor'], 'json'));
        $serviceSetting->setWhitecoatS($this->serializer->serialize($this->data['whitecoat'], 'json'));
        $serviceSetting->setYahooS($this->serializer->serialize($this->data['yelp'], 'json'));
        $serviceSetting->setYelpS($this->serializer->serialize($this->data['yahoo'], 'json'));
        $serviceSetting->setZomatoS($this->serializer->serialize($this->data['zomato'], 'json'));
        $serviceSetting->setUserId($serviceSetting->getUserId()??1);

        return $serviceSetting;
    }

    /**
     * @param SettingData $model
     */

    private function normalizeData(SettingData $model)
    {
        $this->data['facebook'] = $this->serializer->normalize($model, null, array('groups' => array('facebook')));
        $this->data['google'] = $this->serializer->normalize($model, null, array('groups' => array('google')));
        $this->data['ratemyagent'] = $this->serializer->normalize($model, null, array('groups' => array('ratemyagent')));
        $this->data['tripadvisor'] = $this->serializer->normalize($model, null, array('groups' => array('tripadvisor')));
        $this->data['whitecoat'] = $this->serializer->normalize($model, null, array('groups' => array('whitecoat')));
        $this->data['yelp'] = $this->serializer->normalize($model, null, array('groups' => array('yelp')));
        $this->data['yahoo'] = $this->serializer->normalize($model, null, array('groups' => array('yahoo')));
        $this->data['zomato'] = $this->serializer->normalize($model, null, array('groups' => array('zomato')));
    }


    public function deserializeData(ServicesSetting $servicesSetting)
    {
        return $this->serializer->deserialize($servicesSetting->getJsonData(), SettingData::class, 'json');
    }
}