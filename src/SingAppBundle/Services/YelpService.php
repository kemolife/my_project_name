<?php


namespace SingAppBundle\Services;


class YelpService
{
    private $client;

    public function __construct()
    {
        $options = array(
            'apiKey' => 'g6yFy2p6ND9VZ6OgzKz0puQf_GTkM1R1pQJn67iyMtmoBosbslDJs3jcszCSvjveDogKOFnEPCfR9XoQZGGb-B86vTudpot_nkc-GU72TlO_o7fEqFQd3TwBNYU8W3Yx', // Required, unless apiKey is provided
            'apiHost' => 'api.yelp.com', // Optional, default 'api.yelp.com',
        );

        $this->client = \Stevenmaguire\Yelp\ClientFactory::makeWith(
            $options,
            \Stevenmaguire\Yelp\Version::THREE
        );
    }

    public function getBusiness()
    {
        $specialHttpClient = new \GuzzleHttp\Client([
            // ... some special configuration
        ]);
        return $this->client->setHttpClient($specialHttpClient)
            ->getBusiness('the-motel-bar-chicago');
    }
}