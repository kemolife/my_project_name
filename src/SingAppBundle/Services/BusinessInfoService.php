<?php

namespace SingAppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Form\BusinessInfoType;

class BusinessInfoService
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveCreateRelations(BusinessInfo $businessInfo, BusinessInfoType $form)
    {
        $category = new AdditionalCategoriesBusinessInfo();
        $category->setName($form->additionalCategories);

        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(19.99);
        $product->setDescription('Ergonomic and stylish!');

        // relates this product to the category
        $product->setCategory($category);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($category);
        $entityManager->persist($product);
        $entityManager->flush();
    }
}