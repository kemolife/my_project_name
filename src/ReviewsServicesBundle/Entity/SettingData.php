<?php

namespace ReviewsServicesBundle\Entity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SettingData
{
    /**
     * @Groups({"facebook"})
     */
    private $facebook_app_id;
    /**
     * @Groups({"facebook"})
     */
    private $app_secret;
    /**
     * @Groups({"facebook"})
     */
    private $page_id;
    /**
     * @Groups({"facebook"})
     */
    private $token;
    /**
     * @Groups({"google"})
     */
    private $place_id;
    /**
     * @Groups({"google"})
     */
    private $app_key;
    /**
     * @Groups({"ratemyagent"})
     */
    private $agent_key;
    /**
     * @Groups({"tripadvisor"})
     */
    private $tripadvisor_location_id;
    /**
     * @Groups({"tripadvisor"})
     */
    private $tripadvisor_access_token;
    /**
     * @Groups({"whitecoat"})
     */
    private $whitecoat_id;
    /**
     * @Groups({"yelp"})
     */
    private $yelp_business_id;
    /**
     * @Groups({"yelp"})
     */
    private $yelp_access_token;
    /**
     * @Groups({"zomato"})
     */
    private $zomato_business_id;
    /**
     * @Groups({"yahoo"})
     */
    private $yahoo_url;
    /**
     * @Groups({"zomato"})
     */
    private $zomato_access_token;

    /**
     * Set facebookAppId.
     *
     * @param string|null $facebookAppId
     *
     * @return SettingData
     */
    public function setFacebookAppId($facebookAppId = null)
    {
        $this->facebook_app_id = $facebookAppId;

        return $this;
    }

    /**
     * Get facebookAppId.
     *
     * @return string|null
     */
    public function getFacebookAppId()
    {
        return $this->facebook_app_id;
    }

    /**
     * Set appSecret.
     *
     * @param string|null $appSecret
     *
     * @return SettingData
     */
    public function setAppSecret($appSecret = null)
    {
        $this->app_secret = $appSecret;

        return $this;
    }

    /**
     * Get appSecret.
     *
     * @return string|null
     */
    public function getAppSecret()
    {
        return $this->app_secret;
    }

    /**
     * Set pageId.
     *
     * @param string|null $pageId
     *
     * @return SettingData
     */
    public function setPageId($pageId = null)
    {
        $this->page_id = $pageId;

        return $this;
    }

    /**
     * Get pageId.
     *
     * @return string|null
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * Set token.
     *
     * @param string|null $token
     *
     * @return SettingData
     */
    public function setToken($token = null)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set placeId.
     *
     * @param string|null $placeId
     *
     * @return SettingData
     */
    public function setPlaceId($placeId = null)
    {
        $this->place_id = $placeId;

        return $this;
    }

    /**
     * Get placeId.
     *
     * @return string|null
     */
    public function getPlaceId()
    {
        return $this->place_id;
    }

    /**
     * Set appKey.
     *
     * @param string|null $appKey
     *
     * @return SettingData
     */
    public function setAppKey($appKey = null)
    {
        $this->app_key = $appKey;

        return $this;
    }

    /**
     * Get appKey.
     *
     * @return string|null
     */
    public function getAppKey()
    {
        return $this->app_key;
    }

    /**
     * Set agentKey.
     *
     * @param string|null $agentKey
     *
     * @return SettingData
     */
    public function setAgentKey($agentKey = null)
    {
        $this->agent_key = $agentKey;

        return $this;
    }

    /**
     * Get agentKey.
     *
     * @return string|null
     */
    public function getAgentKey()
    {
        return $this->agent_key;
    }

    /**
     * Set tripadvisorLocationId.
     *
     * @param string|null $tripadvisorLocationId
     *
     * @return SettingData
     */
    public function setTripadvisorLocationId($tripadvisorLocationId = null)
    {
        $this->tripadvisor_location_id = $tripadvisorLocationId;

        return $this;
    }

    /**
     * Get tripadvisorLocationId.
     *
     * @return string|null
     */
    public function getTripadvisorLocationId()
    {
        return $this->tripadvisor_location_id;
    }

    /**
     * Set tripadvisorAccessToken.
     *
     * @param string|null $tripadvisorAccessToken
     *
     * @return SettingData
     */
    public function setTripadvisorAccessToken($tripadvisorAccessToken = null)
    {
        $this->tripadvisor_access_token = $tripadvisorAccessToken;

        return $this;
    }

    /**
     * Get tripadvisorAccessToken.
     *
     * @return string|null
     */
    public function getTripadvisorAccessToken()
    {
        return $this->tripadvisor_access_token;
    }

    /**
     * Set whitecoatId.
     *
     * @param string|null $whitecoatId
     *
     * @return SettingData
     */
    public function setWhitecoatId($whitecoatId = null)
    {
        $this->whitecoat_id = $whitecoatId;

        return $this;
    }

    /**
     * Get whitecoatId.
     *
     * @return string|null
     */
    public function getWhitecoatId()
    {
        return $this->whitecoat_id;
    }

    /**
     * Set yelpBusinessId.
     *
     * @param string|null $yelpBusinessId
     *
     * @return SettingData
     */
    public function setYelpBusinessId($yelpBusinessId = null)
    {
        $this->yelp_business_id = $yelpBusinessId;

        return $this;
    }

    /**
     * Get yelpBusinessId.
     *
     * @return string|null
     */
    public function getYelpBusinessId()
    {
        return $this->yelp_business_id;
    }

    /**
     * Set yelpAccessToken.
     *
     * @param string|null $yelpAccessToken
     *
     * @return SettingData
     */
    public function setYelpAccessToken($yelpAccessToken = null)
    {
        $this->yelp_access_token = $yelpAccessToken;

        return $this;
    }

    /**
     * Get yelpAccessToken.
     *
     * @return string|null
     */
    public function getYelpAccessToken()
    {
        return $this->yelp_access_token;
    }

    /**
     * Set zomatoBusinessId.
     *
     * @param string|null $zomatoBusinessId
     *
     * @return SettingData
     */
    public function setZomatoBusinessId($zomatoBusinessId = null)
    {
        $this->zomato_business_id = $zomatoBusinessId;

        return $this;
    }

    /**
     * Get zomatoBusinessId.
     *
     * @return string|null
     */
    public function getZomatoBusinessId()
    {
        return $this->zomato_business_id;
    }

    /**
     * Set zomatoAccessToken.
     *
     * @param string|null $zomatoAccessToken
     *
     * @return SettingData
     */
    public function setZomatoAccessToken($zomatoAccessToken = null)
    {
        $this->zomato_access_token = $zomatoAccessToken;

        return $this;
    }

    /**
     * Get zomatoAccessToken.
     *
     * @return string|null
     */
    public function getZomatoAccessToken()
    {
        return $this->zomato_access_token;
    }

    /**
     * Set yahooUrl.
     *
     * @param string|null $yahooUrl
     *
     * @return SettingData
     */
    public function setYahooUrl($yahooUrl = null)
    {
        $this->yahoo_url = $yahooUrl;

        return $this;
    }

    /**
     * Get yahooUrl.
     *
     * @return string|null
     */
    public function getYahooUrl()
    {
        return $this->yahoo_url;
    }
}
