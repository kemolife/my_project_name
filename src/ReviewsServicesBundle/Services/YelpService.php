<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use ReviewsServicesBundle\Entity\Reviews;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class YelpService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        $id = 'FmGF1B-Rpsjq1f5b56qMwg'; //need real id
        $accessToken = 'eCIiPwclQgJ2lsGTv21dv5TjTRYxLiLp35H6eutuHwRIXhJa5bnDovPzy1ZizhKDeoUpYGfHxc578c-pSU-L087ywJNb54kvJEuhWB9DaDdVgcSkmLNSg6wmBJcXW3Yx'; //need access token
        $url = 'https://api.yelp.com/v3/businesses/' . $id . '/reviews';
        $header = [
            'Authorization: Bearer ' . $accessToken,
            'Content-type: application/json'
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

            foreach ($data->reviews as $item) {
                $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($item->time_created)));
                if ($this->entityManager->getRepository('ReviewsServicesBundle:Reviews')->findUserReview($dateObj, $item->user->name) === null) {
                    $reviews = new Reviews();
                    $reviews->setSite(3);
                    $reviews->setCreated($dateObj);
                    $reviews->setAttribution($item->user->name);
                    $reviews->setRating($item->rating);
                    $reviews->setStatus(1);
                    $reviews->setBody($item->text);

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