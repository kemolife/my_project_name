<?php

namespace ReviewsServicesBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use ReviewsServicesBundle\Entity\Reviews;
use ReviewsServicesBundle\Services\Exceptions\ParserException;

class GoogleService implements ParserInterface
{
    use ParseTrait;

    public $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        try {
            $placeId = 'ChIJ3TH9CwFZwokRIvNO1SP0WLg';
            $key = 'AIzaSyDD5JVD_8OQBUnso8meTcmK6a5Bos9SkmY';
            $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeId."&key=".$key;

            $curl = new Curl();
            $curl->get($url);
            $curl->close();
            if ($curl->error) {
                throw new ParserException($curl->errorMessage);
            }

            foreach ($curl->response->result->reviews as $item) {
                $this->findAndSaveReview($item);
            }
        } catch (\Exception $e) {
            throw new ParserException($e->getMessage());
        }
    }

    public function prepareReview($item)
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $item->time));
        $review = new Reviews();
        $review->setSite(1);
        $review->setCreated($dateObj);
        $review->setAttribution($item->author_name);
        $review->setIdentifier(md5($item->author_url));
        $review->setRating($item->rating);
        $review->setStatus(1);
        $review->setBody($item->text);
        return $review;
    }

}