<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use ReviewsServicesBundle\Entity\Reviews;
use ReviewsServicesBundle\Services\Exceptions\ParserException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RatemyagentService implements ParserInterface
{
    use ParseTrait;

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        $id = 'aa2141'; //need real id
        $url = 'https://api.ratemyagent.com.au/Reviews?AgentCode='.$id.'&Take=1000';
        if(!empty($this->prepareData($this->curlInit($url))->Results)) {
            foreach ($this->prepareData($this->curlInit($url))->Results as $item) {
                $arrayReviewUrl = explode('/',$item->ReviewUrl);
                $urlReview = 'https://api.ratemyagent.com.au/Reviews/Code-'.end($arrayReviewUrl);
                $dataReview = $this->prepareData($this->curlInit($urlReview));
                $this->findAndSaveReview($dataReview);
            }
        } else{
            throw new ParserException();
        }
    }

    private function curlInit($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_TIMEOUT,5000);
        $results = curl_exec($ch);
        curl_close($ch);
        return $results;
    }

    private function prepareData($result)
    {
        return json_decode($result);
    }

    public function prepareReview($item)
    {
        $dataFilterReview = $item->Data->Testimonial;
        $identifier = $dataFilterReview->AgentAssessmentId.'-'.$dataFilterReview->AgentAssessmentCode;
        $rating = ((int)$dataFilterReview->Ratings->Accuracy->Stars
                + (int)$dataFilterReview->Ratings->Communication->Stars
                + (int)$dataFilterReview->Ratings->Trustworthiness->Stars
                + (int)$dataFilterReview->Ratings->Negotiation->Stars
                + (int)$dataFilterReview->Ratings->Overall->Stars)/5;
        $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($dataFilterReview->Date)));
        $review = new Reviews();
        $review->setSite(10);
        $review->setCreated($dateObj);
        $review->setAttribution($dataFilterReview->User->Alias);
        $review->setIdentifier($identifier);
        $review->setRating($rating);
        $review->setStatus(1);
        $review->setBody($dataFilterReview->Description);
        return $review;
    }
}