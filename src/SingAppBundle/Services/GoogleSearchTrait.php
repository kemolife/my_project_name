<?php

namespace SingAppBundle\Services;


use Serps\Core\Browser\Browser;
use Serps\Core\Serp\ItemPosition;
use Serps\HttpClient\CurlClient;
use Serps\SearchEngine\Google\GoogleClient;
use Serps\SearchEngine\Google\GoogleUrl;

trait GoogleSearchTrait
{
    public function getSearchUrl($name, $service)
    {
        $url = null;
        $userAgent = "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.93 Safari/537.36";
        $browserLanguage = "en-US";

        $browser = new Browser(new CurlClient(), $userAgent, $browserLanguage);

        // Create a google client using the browser we configured
        $googleClient = new GoogleClient($browser);

        // Create the url that will be parsed
        $googleUrl = new GoogleUrl();
        $googleUrl->setParam('q', $name.'+'.$service);
        $googleUrl->setLanguageRestriction('lang_en');
        $googleUrl->setAutoCorrectionEnabled(true);

        $response = $googleClient->query($googleUrl);

        $results = $response->getNaturalResults();

        /**
         * @var ItemPosition $result
         */
        foreach($results as $result){
            $url = $result->getDataValue('url');
            $nameTitle = $result->getDataValue('title');
            if(strpos($url, $service) && strpos($nameTitle, $name)){
                return $url;
            }
        }
        return $url;
    }

    public function getElementsByClass(\DOMDocument $document, $className)
    {
        $finder = new \DomXPath($document);
        $nodes = $finder->query("//*[contains(@class, '$className')]");

        return $nodes;
    }
}