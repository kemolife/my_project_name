<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use ReviewsServicesBundle\Entity\Reviews;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ZomatoService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        $id = '308322'; //need real id
        $accessToken = '728cdb6435672349d576c3f845b9b880'; //need access token
        $url = 'https://developers.zomato.com/api/v2.1/reviews?res_id=' . $id;
        $header = [
            'user_key: ' . $accessToken
        ];
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $results = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($results);
            $count = 0;
            foreach ($data->user_reviews as $item) {
                $review = $item->review;
                $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $review->timestamp));
                if ($this->entityManager->getRepository('ReviewsServicesBundle:Reviews')->findUserReview($dateObj, $review->user->name) === null) {
                    $reviews = new Reviews();
                    $reviews->setSite(5);
                    $reviews->setCreated($dateObj);
                    $reviews->setAttribution($review->user->name);
                    $reviews->setRating($review->rating);
                    $reviews->setStatus(1);
                    $reviews->setBody($review->review_text);

                    $this->entityManager->persist($reviews);
                    $this->entityManager->flush();
                    $count++;
                }
            }
        } catch (ErrorException $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }

        return new Response('Add reviews: ' . $count);
    }
}