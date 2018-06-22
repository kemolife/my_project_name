<?php

namespace SingAppBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\Images;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FileUploadListener
{

    private $fileUploadService;


    /**
     * Upload file
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function uploadFile(Images $entity, LifecycleEventArgs $args)
    {
       // $this->fileUploadService->uploadFile($entity);
    }
}