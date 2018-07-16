<?php

namespace SingAppBundle\EntityListener;

use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPhoto;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\Images;
use DateInterval;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\InstagramService;

class InstagramPostEntityListener
{

    private $instagramService;

    public function __construct(InstagramService $instagramService)
    {
        $this->instagramService = $instagramService;
    }

    /**
     * Schedule instagram post.
     *
     * @ORM\PrePersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function setAccount(InstagramPost $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        $repository = $em->getRepository('SingAppBundle:InstagramAccount');
        $instagramAccount = $repository->findOneBy(['user' => $entity->getUser()->getId(), 'business' => $entity->getBusiness()->getId()]);

        if ($instagramAccount instanceof InstagramAccount) {
            $entity->setAccount($instagramAccount);

        }
    }
    
    /**
     * Schedule instagram post.
     *
     * @ORM\PostPersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function createPhotos(InstagramPost $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        if ($entity->getUploadedFiles()) {
            foreach($entity->getUploadedFiles() as $uploadedFile)
            {
                $photo = new InstagramPhoto();

                $photo->setImage($uploadedFile);

                $photo->setPost($entity);

                $em->persist($photo);
                $em->flush();

                $entity->addPhoto($photo);

                unset($uploadedFile);
            }
        }

    }

    /**
     * Schedule instagram post.
     *
     * @ORM\PostPersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function uploadPost(InstagramPost $entity, LifecycleEventArgs $args)
    {
        $this->instagramService->uploadPost($entity);

    }
}
