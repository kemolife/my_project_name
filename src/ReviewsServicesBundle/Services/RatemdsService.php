<?php

namespace ReviewsServicesBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use ReviewsServicesBundle\Entity\Reviews;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RatemdsService
{
    public function execute()
    {

        /// block google captcha !!!!!!!!!!!!!!!!!!!!!!!!!!!1
        $id = '2486418/Dr-ELIZABETH+S.-BARNES-Greensboro-NC.html'; //need real id
        $url = 'https://www.ratemds.com/api/doctor_rating'.$id;
        try {
            $curl = new Curl();

            $curl->get($url);
            $curl->setCookie('cf_clearance', '71c4c40f257431d293f695e2a77075e3dd57ce64-1528289782-2592000');
            $curl->close();
            if ($curl->error) {
                echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            } else {
                echo 'Response:' . "\n";
                var_dump($curl->response);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            $results = curl_exec($ch);
            curl_close($ch);
            var_dump($results);
            $data = json_decode($results);

//            $count = 0;
//            foreach ($data->user_reviews as $item) {
//                $review = $item->review;
//                $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', $review->timestamp));
//                if ($this->entityManager->getRepository('ReviewsServicesBundle:Reviews')->findUserReview($dateObj, null) === null) {
//                    $reviews = new Reviews();
//                    $reviews->setSite(8);
//                    $reviews->setCreated($dateObj);
//                    $reviews->setAttribution(null);
//                    $reviews->setRating($review->rating);
//                    $reviews->setStatus(1);
//                    $reviews->setBody($review->review_text);
//
//                    $this->entityManager->persist($reviews);
//                    $this->entityManager->flush();
//                    $count++;
//                }
//            }
        } catch (ErrorException $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }

        return new Response('Add reviews: ' . $count);
    }
}