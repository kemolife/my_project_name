<?php


namespace SingAppBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Form\BusinessInfoType;

class BusinessInfoEntityListener
{
    /**
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @ORM\PostUpdate
     * @ORM\PostPersist
     */
    public function test(BusinessInfo $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        foreach (['test1', 'test2'] as $name) {
            $additionalCategory = new AdditionalCategoriesBusinessInfo();
            $additionalCategory->setBusiness($entity);
            $additionalCategory->setName($name);


            $em->persist($additionalCategory);
        }

        $em->flush();
    }

}