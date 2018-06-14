<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use ReviewsServicesBundle\Entity\Reviews;
use ReviewsServicesBundle\Services\Exceptions\ParserException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FacebookService implements ParserInterface
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
            $fb = new Facebook([
                'app_id' => '173753973300728',
                'app_secret' => '4ccd427f6d8e6353dbd5531a476d9c65',
                'default_graph_version' => 'v3.0',
            ]);
            $response = $fb->get(
                '/249064812322593/ratings',
                'EAACeBzZCbXfgBADRGmfF4irMYWfrbbUbjXmoqzwq2dkZCdY2uDq4k1jZB4vcsZBisuspJXXRPHLRSsU3vBJSuujbT7po8Gz8DHeaYFp1d3rOoZCbcnZAuc6KPZCYIPeFCj3qBY9XxJZCriZCJLTW8Jz94V1ckT1bX5qY4PS5d61mTzG0sp8bAztSNH34o0RdF5lAZD'
            );
            $graphNode = $response->getGraphEdge();
            $data = json_decode($graphNode);
            foreach ($data as $item) {
                $this->findAndSaveReview($item);
            }
        } catch (FacebookResponseException | FacebookSDKException $e) {
            throw new ParserException($e->getMessage());
        }
    }

    public function prepareReview($item)
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($item->created_time->date)));
        $reviewerName = null;
        if(property_exists($item, 'reviewer')){
            $reviewerName = $item->reviewer->name;
        }
        $review = new Reviews();
        $review->setSite(2);
        $review->setCreated($dateObj);
        $review->setAttribution($reviewerName);
        $review->setIdentifier(md5($item->created_time->date.'-'.$item->review_text));
        $review->setRating($item->rating);
        $review->setStatus(1);
        $review->setBody($item->review_text);
        return $review;
    }

}