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
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\SetBusinessListener"
 *     })
 */
class FacebookAccount extends SocialNetworkAccount
{
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $accessToken;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $expiresIn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $page;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $pageAccessToken;


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

    /**
     * Set user
     *
     * @param User $user
     *
     * @return FacebookAccount
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
     * Set page.
     *
     * @param string|null $page
     *
     * @return FacebookAccount
     */
    public function setPage($page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page.
     *
     * @return string|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set pageAccessToken.
     *
     * @param string|null $pageAccessToken
     *
     * @return FacebookAccount
     */
    public function setPageAccessToken($pageAccessToken = null)
    {
        $this->pageAccessToken = $pageAccessToken;

        return $this;
    }

    /**
     * Get pageAccessToken.
     *
     * @return string|null
     */
    public function getPageAccessToken()
    {
        return $this->pageAccessToken;
    }
}
