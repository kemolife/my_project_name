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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $videoId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $channelId;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $visibility;

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

    /**
     * Set videoId.
     *
     * @param string|null $videoId
     *
     * @return YoutubePost
     */
    public function setVideoId($videoId = null)
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Get videoId.
     *
     * @return string|null
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * Set channelId.
     *
     * @param string|null $channelId
     *
     * @return YoutubePost
     */
    public function setChannelId($channelId = null)
    {
        $this->channelId = $channelId;

        return $this;
    }

    /**
     * Get channelId.
     *
     * @return string|null
     */
    public function getChannelId()
    {
        return $this->channelId;
    }

    /**
     * Set visibility.
     *
     * @param string $visibility
     *
     * @return YoutubePost
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility.
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
}
