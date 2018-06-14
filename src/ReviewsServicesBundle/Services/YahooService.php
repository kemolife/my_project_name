<?php

namespace ReviewsServicesBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMXPath;
use ReviewsServicesBundle\Entity\Reviews;
use ReviewsServicesBundle\Services\Exceptions\ParserException;

class YahooService implements ParserInterface
{
    use ParseTrait;

    private static $siteMapBlockId =
        [
            'info-div',
            'main',
            'RightColumn'
        ];

    private $entityManager;
    private $url;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute()
    {
        $this->url = 'https://www.stringsandbeyond.com'; // need clients
        $urlSiteMap = $this->url . '/ind.html';
        libxml_use_internal_errors(true);
        try {
            $doc = $this->linkParse($urlSiteMap);
            $this->parseSiteMap($doc);
        } catch (\Exception $e) {
            throw new ParserException($e->getMessage());
        }

    }

    public function linkParse($url): DOMdocument
    {
        $html = file_get_contents($url);
        $doc = new DOMdocument();
        $doc->loadHTML($html);
        return $doc;
    }

    public function parseSiteMap(DOMDocument $doc)
    {
        $node = null;
        foreach (self::$siteMapBlockId as $id){
            if($doc->getElementById($id) !== null) {
                $node = $doc->getElementById($id);
                break;
            }
        }
        if($node !== null) {
            $linkNodes = $node->getElementsByTagName('a');
            foreach ($linkNodes as $key => $linkNode) {
                $link = $linkNode->getAttribute('href');
                if (preg_match("/.html/", $link)) {
                    $doc = $this->linkParse($this->url . '/' . $link);
                    $this->findReviewsClass($doc);
                }
            }
        }else{
            throw new ParserException('Problem with yahoo business site site-map');
        }
    }

    public function findReviewsClass($doc)
    {
        $node = $this->findForClass($doc, 'pdReviewsDisplay');
        if ($node->length) {
            $nodes = $this->findForClass($doc, 'pdPrWrapperInner', $node->item(0));
            foreach ($nodes as $key => $item) {
                $this->parseReview($key, $doc);
            }
        }
    }

    public function findForClass($doc, $class, $node = null)
    {
        $finder = new DomXPath($doc);
        return $finder->query("//*[contains(@class, '$class')]", $node);
    }

    public function parseReview($key, DOMdocument $doc)
    {
        $result = new \stdClass();
        $result->text = utf8_encode(trim($this->findForClass($doc, 'pdPrBody')->item($key)->nodeValue));
        $result->author_name = utf8_encode(trim(substr($this->findForClass($doc, 'pdPrReviewsName')->item($key)->nodeValue, 2)));
        $result->time = $this->findForClass($doc, 'pdPrReviewDate')->item($key)->nodeValue;
        $result->rating = $this->findForClass($doc, 'pdPrListOverallRating')->item($key)->nodeValue;
        $result->rating = $this->findForClass($doc, 'pdPrListOverallRating')->item($key)->nodeValue;
        $result->identifier = md5(trim(substr($this->findForClass($doc, 'pdPrReviewsName')->item($key)->nodeValue, 2)).'-'.
            $this->findForClass($doc, 'pdPrReviewDate')->item($key)->nodeValue);
        foreach ($this->findForClass($doc, 'pdPrListOverallRating')->item($key)->childNodes as $childNode) {
            $result->rating = substr(explode(' ', $childNode->getAttribute('class'))[2], 2);
        }
        $this->findAndSaveReview($result);
    }

    public function prepareReview($item)
    {
        $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($item->time)));
        $review = new Reviews();
        $review->setSite(7);
        $review->setCreated($dateObj);
        $review->setAttribution($item->author_name);
        $review->setIdentifier($item->identifier);
        $review->setRating($item->rating);
        $review->setStatus(1);
        $review->setBody($item->text);
        return $review;
    }
}