<?php

namespace SingAppBundle\Form\DataTransformers;

use SingAppBundle\Entity\BusinessImage;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class BusinessImageTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @return string
     */
    public function transform($photos)
    {
        return null;
    }

    /**
     * @param mixed $photos
     * @return array|mixed
     */
    public function reverseTransform($photos)
    {
        $businessImageArray = [];
        foreach ($photos->first()->getImage() as $photo){
            $businessImage = new BusinessImage();
            $businessImage->setImage($photo);
            $businessImageArray[] = $businessImage;
        }
        return $businessImageArray;
    }
}