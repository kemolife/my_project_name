<?php

namespace ReviewsServicesBundle\Services;


use ReviewsServicesBundle\Entity\Reviews;

trait ParseTrait
{
    protected function saveReview($model)
    {
        $this->entityManager->persist($model);
        $this->entityManager->flush();
    }

    protected function findReview(Reviews $review) : ?Reviews
    {
        return $this->entityManager->getRepository('ReviewsServicesBundle:Reviews')->
        findUserReview($review->getCreated(), $review->getAttribution(), $review->getIdentifier());
    }

    public function findAndSaveReview($item)
    {
        $review = $this->prepareReview($item);
        if ($this->findReview($review) === null) {
            $this->saveReview($review);
        }

    }
}