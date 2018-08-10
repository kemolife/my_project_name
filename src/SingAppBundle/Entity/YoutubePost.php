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
class YoutubePost extends Post implements HasOwnerInterface
{

    /**
     * @ORM\ManyToOne(targetEntity="SocialNetworkAccount")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

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
     * @return YoutubePost
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
}
