<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * GoogleAccount.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\SetBusinessListener"
 *     })
 */
class GoogleAccount extends SocialNetworkAccount
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $accessToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $refreshToken;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $expiresIn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $location;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $googleId;

    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return GoogleAccount
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
     * Set refreshToken
     *
     * @param string $refreshToken
     *
     * @return GoogleAccount
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set expiresIn
     *
     * @param \DateTime $expiresIn
     *
     * @return GoogleAccount
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
     * @return GoogleAccount
     */
    public function setCreated($created)
    {
        $this->created = $created;

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
     * @return GoogleAccount
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

    /**
     * Set user
     *
     * @param User $user
     *
     * @return GoogleAccount
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return GoogleAccount
     */
    public function setLocation($location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set googleId.
     *
     * @param string|null $googleId
     *
     * @return GoogleAccount
     */
    public function setGoogleId($googleId = null)
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * Get googleId.
     *
     * @return string|null
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }
}
