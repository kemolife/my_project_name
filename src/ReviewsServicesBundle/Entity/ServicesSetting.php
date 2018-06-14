<?php

namespace ReviewsServicesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServicesSetting
 *
 * @ORM\Table(name="services_setting")
 * @ORM\Entity(repositoryClass="ReviewsServicesBundle\Repository\ServicesSettingRepository")
 */
class ServicesSetting
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="facebook_s", type="string", length=255, nullable=true)
     */
    private $facebookS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_s", type="string", length=255, nullable=true)
     */
    private $googleS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="ratemyagent_s", type="string", length=255, nullable=true)
     */
    private $ratemyagentS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tripadvisor_s", type="string", length=255, nullable=true)
     */
    private $tripadvisorS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="whitecoat_s", type="string", length=255, nullable=true)
     */
    private $whitecoatS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="yahoo_s", type="string", length=255, nullable=true)
     */
    private $yahooS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="yelp_s", type="string", length=255, nullable=true)
     */
    private $yelpS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zomato_s", type="string", length=255, nullable=true)
     */
    private $zomatoS;

    /**
     * @var string|null
     *
     * @ORM\Column(name="user_id", type="integer", length=11, nullable=false)
     */
    private $user_id;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set facebookS.
     *
     * @param string|null $facebookS
     *
     * @return ServicesSetting
     */
    public function setFacebookS($facebookS = null)
    {
        $this->facebookS = $facebookS;

        return $this;
    }

    /**
     * Get facebookS.
     *
     * @return object|null
     */
    public function getFacebookS()
    {
        return json_decode($this->facebookS, true);
    }

    /**
     * Set googleS.
     *
     * @param string|null $googleS
     *
     * @return ServicesSetting
     */
    public function setGoogleS($googleS = null)
    {
        $this->googleS = $googleS;

        return $this;
    }

    /**
     * Get googleS.
     *
     * @return object|null
     */
    public function getGoogleS()
    {
        return json_decode($this->googleS, true);
    }

    /**
     * Set ratemyagentS.
     *
     * @param string|null $ratemyagentS
     *
     * @return ServicesSetting
     */
    public function setRatemyagentS($ratemyagentS = null)
    {
        $this->ratemyagentS = $ratemyagentS;

        return $this;
    }

    /**
     * Get ratemyagentS.
     *
     * @return object|null
     */
    public function getRatemyagentS()
    {
        return json_decode($this->ratemyagentS, true);
    }

    /**
     * Set tripadvisorS.
     *
     * @param string|null $tripadvisorS
     *
     * @return ServicesSetting
     */
    public function setTripadvisorS($tripadvisorS = null)
    {
        $this->tripadvisorS = $tripadvisorS;

        return $this;
    }

    /**
     * Get tripadvisorS.
     *
     * @return object|null
     */
    public function getTripadvisorS()
    {
        return json_decode($this->tripadvisorS, true);
    }

    /**
     * Set whitecoatS.
     *
     * @param string|null $whitecoatS
     *
     * @return ServicesSetting
     */
    public function setWhitecoatS($whitecoatS = null)
    {
        $this->whitecoatS = $whitecoatS;

        return $this;
    }

    /**
     * Get whitecoatS.
     *
     * @return object|null
     */
    public function getWhitecoatS()
    {
        return json_decode($this->whitecoatS, true);
    }

    /**
     * Set yahooS.
     *
     * @param string|null $yahooS
     *
     * @return ServicesSetting
     */
    public function setYahooS($yahooS = null)
    {
        $this->yahooS = $yahooS;

        return $this;
    }

    /**
     * Get yahooS.
     *
     * @return object|null
     */
    public function getYahooS()
    {
        return json_decode($this->yahooS, true);
    }

    /**
     * Set yelpS.
     *
     * @param string|null $yelpS
     *
     * @return ServicesSetting
     */
    public function setYelpS($yelpS = null)
    {
        $this->yelpS = $yelpS;

        return $this;
    }

    /**
     * Get yelpS.
     *
     * @return object|null
     */
    public function getYelpS()
    {
        return json_decode($this->yelpS, true);
    }

    /**
     * Set zomatoS.
     *
     * @param string|null $zomatoS
     *
     * @return ServicesSetting
     */
    public function setZomatoS($zomatoS = null)
    {
        $this->zomatoS = $zomatoS;

        return $this;
    }

    /**
     * Get zomatoS.
     *
     * @return object|null
     */
    public function getZomatoS()
    {
        return json_decode($this->zomatoS, true);
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return ServicesSetting
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    public function getJsonData()
    {
        $data = (array)$this->getGoogleS()
            +(array)$this->getFacebookS()
            +(array)$this->getRatemyagentS()
            +(array)$this->getTripadvisorS()
            +(array)$this->getWhitecoatS()
            +(array)$this->getYahooS()
            +(array)$this->getYelpS()
            +(array)$this->getZomatoS();
        return json_encode($data);
    }
}
