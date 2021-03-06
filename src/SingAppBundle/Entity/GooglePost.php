<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * GooglePost.
 *
 * @ORM\Entity()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\SetBusinessListener",
 *     "SingAppBundle\EntityListener\PostEntityListener"})
 */
class GooglePost extends Post implements HasOwnerInterface
{

    /**
     * @ORM\ManyToOne(targetEntity="SocialNetworkAccount")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $googlePostName;

    /**
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->media = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set account.
     *
     * @param \SingAppBundle\Entity\SocialNetworkAccount|null $account
     *
     * @return GooglePost
     */
    public function setAccount(\SingAppBundle\Entity\SocialNetworkAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \SingAppBundle\Entity\SocialNetworkAccount|null
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set googlePostName.
     *
     * @param string|null $googlePostName
     *
     * @return GooglePost
     */
    public function setGooglePostName($googlePostName = null)
    {
        $this->googlePostName = $googlePostName;

        return $this;
    }

    /**
     * Get googlePostName.
     *
     * @return string|null
     */
    public function getGooglePostName()
    {
        return $this->googlePostName;
    }
}
