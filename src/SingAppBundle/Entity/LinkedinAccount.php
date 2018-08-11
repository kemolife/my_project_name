<?php

namespace SingAppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PinterestAccount.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\SetBusinessListener"
 *     })
 */
class LinkedinAccount extends SocialNetworkAccount
{
    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $accessToken;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $expiration;

    /**
     * Set user
     *
     * @param User $user
     *
     * @return LinkedinAccount
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
     * Set accessToken.
     *
     * @param string $accessToken
     *
     * @return LinkedinAccount
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
     * Set expiration.
     *
     * @param string|null $expiration
     *
     * @return LinkedinAccount
     */
    public function setExpiration($expiration = null)
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * Get expiration.
     *
     * @return string|null
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
}
