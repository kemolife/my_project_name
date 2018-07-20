<?php

namespace SingAppBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\Media;
use SingAppBundle\Services\PhotoService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaEntityListener
{
    private $photoService;


    /**
     * @param PhotoService $photoService
     */
    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;

    }

    /**
     * Upload media.
     *
     * @ORM\PrePersist()
     *
     * @param LifecycleEventArgs $args
     */
    public function upload(Media $entity, LifecycleEventArgs $args)
    {
        $file = $entity->getPath();

        if (!$file instanceof UploadedFile) {
            return;
        }

        $path = $this->photoService->upload($file, $entity->getUser()->getId());

        $entity->setPath($path);
    }


    /**
     * Remove media.
     *
     * @ORM\PreRemove()
     *
     * @param LifecycleEventArgs $args
     */
    public function removePhotos(Media $entity, LifecycleEventArgs $args)
    {
        $path = $entity->getPath();

        $this->deleteResizePhoto($path);
    }

    private function deleteResizePhoto($photo)
    {
        $photoDir = pathinfo($photo, PATHINFO_DIRNAME);
        $fileName = pathinfo($photo, PATHINFO_FILENAME);

        $files = scandir($photoDir);
        foreach ($files as $file) {
            if (0 === strpos($file, $fileName)) {
                unlink($photoDir . '/' . $file);
            }
        }
    }
}
