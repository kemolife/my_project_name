<?php

namespace SingAppBundle\EntityListener;

use JMS\JobQueueBundle\Entity\Job;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\GooglePost;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPhoto;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\Images;
use DateInterval;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\Media;
use SingAppBundle\Entity\Post;
use SingAppBundle\Entity\YoutubeAccount;
use SingAppBundle\Entity\YoutubePost;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\GoogleService;
use SingAppBundle\Services\InstagramService;
use SingAppBundle\Services\YoutubeService;


class PostEntityListener
{

    private $instagramService;
    private $youtubeService;
    private $googleService;

    public function __construct(InstagramService $instagramService, GoogleService $googleService, YoutubeService $youtubeService)
    {
        $this->instagramService = $instagramService;
        $this->googleService = $googleService;
        $this->youtubeService = $youtubeService;
    }

    /**
     * Schedule instagram post.
     *
     * @ORM\PrePersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function setAccount(Post $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        if ($entity instanceof InstagramPost) {
            $repository = $em->getRepository('SingAppBundle:InstagramAccount');
            $instagramAccount = $repository->findOneBy(['user' => $entity->getUser()->getId(), 'isDefault' => 1]);

            if ($instagramAccount instanceof InstagramAccount) {
                $entity->setAccount($instagramAccount);
            } else {
                throw new OAuthCompanyException('Cannot create post without default account.');
            }
        }
        elseif ($entity instanceof GooglePost) {
            $repository = $em->getRepository('SingAppBundle:GoogleAccount');

            $googleAccount = $repository->findOneBy(['user' => $entity->getUser()->getId(), 'business' => $entity->getBusiness()->getId()]);

            if ($googleAccount instanceof GoogleAccount) {
                $entity->setAccount($googleAccount);
            }
        }
        elseif ($entity instanceof YoutubePost) {
            $repository = $em->getRepository('SingAppBundle:YoutubeAccount');

            $youtubeAccount = $repository->findOneBy(['user' => $entity->getUser()->getId(), 'business' => $entity->getBusiness()->getId()]);

            if ($youtubeAccount instanceof YoutubeAccount) {
                $entity->setAccount($youtubeAccount);
            }
        }
    }

    /**
     * Schedule post.
     *
     * @ORM\PostPersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function createMedia(Post $entity, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        if ($entity->getUploadedFiles()) {
            foreach ($entity->getUploadedFiles() as $uploadedFile) {
                $media = new Media();

                $media->setPath($uploadedFile);

                $media->setPost($entity);
                $media->setIsUsed(1);

                $em->persist($media);
                $em->flush();

                $entity->addMedia($media);

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
    public function schedule(Post $entity, LifecycleEventArgs $args)
    {

        if ($entity->getSchedule()) {
            $postDate = $entity->getPostDate();

            if ($entity->getTimezoneOffset() != 0) {
                $postDate = $postDate->modify($entity->getTimezoneOffset() . ' hours');
            }

            $em = $args->getEntityManager();

            $job = new Job('app:post:upload', array($entity->getId()));
            $job->setExecuteAfter($postDate);
            $em->persist($job);

            $entity->setStatus('pending');
            $em->persist($entity);

            $em->flush();
        } elseif ($entity instanceof InstagramPost) {
            $this->instagramService->uploadPost($entity);
        }
        elseif ($entity instanceof GooglePost) {
            $this->googleService->createPost($entity);
        }
        elseif ($entity instanceof YoutubePost) {
            $this->youtubeService->createVideo($entity);
        }

    }

    /**
     * Remove post from instagram
     *
     * @ORM\PreRemove()
     *
     * @param LifecycleEventArgs $args
     */
    public function removePostFrom(Post $entity, LifecycleEventArgs $args)
    {
        if ($entity instanceof InstagramPost && $entity->getStatus() == 'posted') {
            $this->instagramService->removePost($entity);
        }
        elseif ($entity instanceof GooglePost && $entity->getStatus() == 'posted')
        {
            $this->googleService->removePost($entity);
        }
        elseif ($entity instanceof YoutubePost && $entity->getStatus() == 'posted')
        {
            $this->youtubeService->removePost($entity);
        }
    }
}
