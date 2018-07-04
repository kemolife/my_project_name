<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FacebookAccount.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class FacebookAccount extends SocialNetworkAccount
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $accessToken;


    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $expiresIn;


    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return FacebookAccount
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set expiresIn
     *
     * @param \DateTime $expiresIn
     *
     * @return FacebookAccount
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Get expiresIn
     *
     * @return \DateTime
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return FacebookAccount
     * @ORM\PrePersist()
     */
    public function setCreated($created)
    {
        $this->created = new \DateTime();

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set business
     *
     * @param BusinessInfo $business
     *
     * @return FacebookAccount
     */
    public function setBusiness(BusinessInfo $business = null)
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Get business
     *
     * @return BusinessInfo
     */
    public function getBusiness()
    {
        return $this->business;
    }
}
