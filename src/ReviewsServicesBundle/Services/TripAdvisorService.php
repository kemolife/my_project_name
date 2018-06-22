<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use ReviewsServicesBundle\Entity\Reviews;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TripAdvisorService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        try {
            $reviews = new exussum12\TripAdvisor\Reviews('your key', 'your secret');
            foreach ($reviews->get() as $item) {
                $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($item->time_created)));
                if ($this->entityManager->getRepository('ReviewsServicesBundle:Reviews')->findUserReview($dateObj, $item->user->name) === null) {
                    $reviews = new Reviews();
                    $reviews->setSite(4);
                    $reviews->setCreated($dateObj);
                    $reviews->setAttribution($item->user->name);
                    $reviews->setRating($item->rating);
                    $reviews->setStatus(1);
                    $reviews->setBody($item->text);

                    $this->entityManager->persist($reviews);
                    $this->entityManager->flush();
                }
            }
        } catch (ErrorException $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }

        return new Response('Add reviews: ' . $count);
    }

}