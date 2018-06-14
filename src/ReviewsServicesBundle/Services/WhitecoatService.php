<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use ReviewsServicesBundle\Entity\Reviews;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WhitecoatService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        $id = 891060; //need real id
        $url = 'https://www.whitecoat.com.au/directory/api/practice/getPracticeByProviderId?id='.$id.'&modalityId=901';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_TIMEOUT,5000);
            $results = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($results);
            $count = 0;

            foreach ($data->practiceDetails->comments as $item) {
                $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($item->createdDate)));
                if ($this->entityManager->getRepository('ReviewsServicesBundle:Reviews')->findUserReview($dateObj, null, $item->reviewId) === null) {
                    $reviews = new Reviews();
                    $reviews->setSite(9);
                    $reviews->setCreated($dateObj);
                    $reviews->setAttribution(null);
                    $reviews->setIdentifier($item->reviewId);
                    $reviews->setRating(5);
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