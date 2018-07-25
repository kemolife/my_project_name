<?php

namespace SingAppBundle\EntityListener;

use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\Images;
use DateInterval;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\PinterestPhoto;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\PinterestService;

class PinterestPinEntityListener
{

    private $pinterestService;

    public function __construct(PinterestService $pinterestService)
    {
        $this->pinterestService = $pinterestService;
    }

    /**
     * Schedule instagram post.
     *
     * @ORM\PrePersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function setAccount(PinterestPin $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        $repository = $em->getRepository('SingAppBundle:PinterestAccount');
        $pinterestAccount = $repository->findOneBy(['user' => $entity->getUser()->getId(), 'business' => $entity->getBusiness()->getId()]);

        if ($pinterestAccount instanceof PinterestAccount) {
            $entity->setAccount($pinterestAccount);

        }
    }
    
    /**
     * Schedule instagram post.
     *
     * @ORM\PostPersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function createPhotos(PinterestPin $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        if ($entity->getUploadedFiles()) {
            foreach($entity->getUploadedFiles() as $uploadedFile)
            {
                $photo = new PinterestPhoto();

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
    public function uploadPost(PinterestPin $entity, LifecycleEventArgs $args)
    {
        $this->pinterestService->createPin($entity);

    }
}
