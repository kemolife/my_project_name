<?php

namespace SingAppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\PhotosBusinessInfo;
use SingAppBundle\Form\BusinessInfoType;

class BusinessInfoService
{
    private $entityManager;
    private $businessInfo;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveCreateRelations(BusinessInfo $businessInfo)
    {
        $this->createAdditionCategories($businessInfo);
        $this->createPhotos($businessInfo);
    }

    private function createAdditionCategories($businessInfo)
    {
        $category = new AdditionalCategoriesBusinessInfo();

        $category->setName($this->businessInfo->getAdditionalCategoriesField());
        $category->setBusinessId($this->businessInfo->getId());

        $this->entityManager->persist($category);
        $this->entityManager->flush();

    }

    private function createPhotos($businessInfo)
    {
        $photos = new PhotosBusinessInfo();

        $photos->setName($this->businessInfo->getPhotosField());
        $photos->setBusinessId($this->businessInfo->getId());

        $this->entityManager->persist($photos);
        $this->entityManager->flush();
    }
}