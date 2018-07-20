<?php

namespace SingAppBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhotoService
{
    /** @var string */
    private $webDir;

    /** @var string */
    private $baseDir;

    /**
     * ImageService constructor.
     *
     * @param string $webDir
     * @param string $baseDir
     */
    public function __construct($webDir, $baseDir)
    {
        $this->webDir = $webDir;
        $this->baseDir = $baseDir;
    }

    /**
     * @param string $linkedEntityType
     * @param int    $linkedEntityId
     * @param string $name
     * @param string $width
     * @param string $height
     * @param string $format
     * @param int    $zoomCrop
     *
     * @return string
     */
    public function resize($photo, $width, $height) {
        $filePath = $this->webDir.$photo;
        if (file_exists($filePath)) {
            $path_parts = pathinfo($filePath);

            $resizedPath = $this->webDir.'/'.$this->baseDir.'/resized/'.$path_parts['filename'].$path_parts['extension'];

            $this->createResize($filePath, $resizedPath, $width, $height);

        }
        else {
            $resizedPath = $filePath;
        }


        return $resizedPath;
    }

    public function upload(UploadedFile $file, $targetDir, $convertToJPG = false)
    {
        $fullPath = $this->webDir.'/'.$this->baseDir.'/'.$targetDir.'/'.'photos';

        $format = $file->getClientOriginalExtension();
        $fileName = uniqid().'.'.$format;
        $file->move($fullPath, $fileName);
        $filePath = $this->baseDir.'/'.$targetDir.'/'.'photos'.'/'.$fileName;

        if ($convertToJPG) {
            $filePath = $this->convertToJPG($filePath);
        }

        return $filePath;
    }

    private function createResize($imagePath, $resizedPath, $width, $height)
    {
        $image = new \Imagick(realpath($imagePath));

        if ('auto' === $height) {
            $info = getimagesize($imagePath);
            list($width_old, $height_old) = $info;

            $factor = $width / $width_old;

            $final_width = $width;
            $final_height = round($height_old * $factor);

            $image->resizeImage($final_width, $final_height, \Imagick::FILTER_LANCZOS, 1);
        } else {
            $image->cropThumbnailImage($width, $height);
        }
        $image->writeImage($resizedPath);
    }

    public function saveProgressiveJPG($filePath)
    {
        $image = new \Imagick(realpath($filePath));

        $image->thumbnailImage(500, 0);
        $image->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
        $image->blurImage(20, 10);
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $progressiveFilePath = str_replace($fileName, $fileName.'_progressive', $filePath);

        $image->writeImage($progressiveFilePath);

        return $progressiveFilePath;
    }

    private function convertToJPG($imagePath)
    {
        $image = new \Imagick(realpath($imagePath));
        $currentFormat = $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
        $newImagePath = $imagePath;
        if ('jpg' != $currentFormat) {
            $newImagePath = str_ireplace($currentFormat, 'jpg', $imagePath);
            unlink($imagePath);
        }

        $image->setImageFormat('jpg');
        $image->writeImage($newImagePath);

        return $newImagePath;
    }
}
