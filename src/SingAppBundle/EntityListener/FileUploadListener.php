<?php

namespace SingAppBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\Images;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FileUploadListener
{
    /**
     * Upload photo.
     *
     * @ORM\PrePersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function upload(Images $entity, LifecycleEventArgs $args)
    {
        $file = $entity->getImage();
        if (!$file instanceof UploadedFile) {
            return;
        }

        $fullPath = 'photos';

        $format = $file->getClientOriginalExtension();
        $fileName = uniqid().'.'.$format;
        $file->move($fullPath, $fileName);
        $filePath = 'photos'.'/'.$fileName;


        $entity->setImage($filePath);
    }
}