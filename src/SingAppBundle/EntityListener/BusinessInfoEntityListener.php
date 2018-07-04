<?php


namespace SingAppBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessImage;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Form\BusinessInfoType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            $additionalCategory->addBusiness($entity);
            $additionalCategory->setName($name);


            $em->persist($additionalCategory);
        }

        $em->flush();
    }

    /**
     * @param BusinessInfo $entity
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function createPhotos(BusinessInfo $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        if ($entity->getUploadedFiles()) {
            foreach ($entity->getUploadedFiles() as $uploadedFile) {
                $photo = new BusinessImage();

                $photo->setImage($uploadedFile);

                $photo->setBusinessInfo($entity);

                $em->persist($photo);
                $em->flush();

                unset($uploadedFile);
            }
        }

    }

    /**
     * @param BusinessInfo $entity
     * @param LifecycleEventArgs $args
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function saveLogo(BusinessInfo $entity, LifecycleEventArgs $args)
    {
        $changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
        if(array_key_exists('logo', $changes) && $changes['logo'][1] instanceof UploadedFile || $entity->getLogo() instanceof UploadedFile) {
            $fullPath = 'photos/logo';

            $format = $entity->getLogo()->getClientOriginalExtension();
            $fileName = uniqid() . '.' . $format;
            $entity->getLogo()->move($fullPath, $fileName);
            $filePath = 'photos/logo' . '/' . $fileName;


            $entity->setLogo($filePath);
        }elseif(array_key_exists('logo', $changes) && empty($changes['logo'][1])){
            $entity->setLogo($changes['logo'][0]);
        }
    }

}