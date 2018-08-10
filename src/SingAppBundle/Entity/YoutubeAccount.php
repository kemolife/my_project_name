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
class YoutubeAccount extends SocialNetworkAccount
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
    protected $googleId;

    /**
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
    }


    /**
     * Set accessToken.
     *
     * @param string $accessToken
     *
     * @return YoutubeAccount
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set refreshToken.
     *
     * @param string|null $refreshToken
     *
     * @return YoutubeAccount
     */
    public function setRefreshToken($refreshToken = null)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set expiresIn.
     *
     * @param \DateTime $expiresIn
     *
     * @return YoutubeAccount
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Get expiresIn.
     *
     * @return \DateTime
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * Set googleId.
     *
     * @param string|null $googleId
     *
     * @return YoutubeAccount
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
