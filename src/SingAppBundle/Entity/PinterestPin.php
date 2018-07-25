<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PinterestPin.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\PinterestPinEntityListener"})
 */
class PinterestPin  extends Post implements HasOwnerInterface
{


    /**
     * @ORM\ManyToOne(targetEntity="SocialNetworkAccount")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $mediaId;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $board;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set mediaId
     *
     * @param string $mediaId
     *
     * @return PinterestPin
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * Get mediaId
     *
     * @return string
     */
    public function getMediaId()
    {
        return $this->mediaId;
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
     * Set title
     *
     * @param string $title
     *
     * @return PinterestPin
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return PinterestPin
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set status
     *
     * @param enum_post_status_type $status
     *
     * @return PinterestPin
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return enum_post_status_type
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set postDate
     *
     * @param \DateTime $postDate
     *
     * @return PinterestPin
     */
    public function setPostDate($postDate)
    {
        $this->postDate = $postDate;

        return $this;
    }

    /**
     * Get postDate
     *
     * @return \DateTime
     */
    public function getPostDate()
    {
        return $this->postDate;
    }

    /**
     * Set account
     *
     * @param SocialNetworkAccount $account
     *
     * @return PinterestPin
     */
    public function setAccount(SocialNetworkAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return SocialNetworkAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Add photo
     *
     * @param Images $photo
     *
     * @return PinterestPin
     */
    public function addPhoto(Images $photo)
    {
        $this->photos[] = $photo;

        return $this;
    }

    /**
     * Remove photo
     *
     * @param Images $photo
     */
    public function removePhoto(Images $photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * Set socialNetwork
     *
     * @param string $socialNetwork
     *
     * @return PinterestPin
     */
    public function setSocialNetwork($socialNetwork)
    {
        $this->socialNetwork = $socialNetwork;

        return $this;
    }

    /**
     * Get socialNetwork
     *
     * @return string
     */
    public function getSocialNetwork()
    {
        return $this->socialNetwork;
    }

    /**
     * Set schedule.
     *
     * @param int $schedule
     *
     * @return PinterestPin
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule.
     *
     * @return int
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
    }

    /**
     * Set board.
     *
     * @param string $board
     *
     * @return PinterestPin
     */
    public function setBoard($board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board.
     *
     * @return string
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set link.
     *
     * @param string|null $link
     *
     * @return PinterestPin
     */
    public function setLink($link = null)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link.
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set imageUrl.
     *
     * @param string|null $imageUrl
     *
     * @return PinterestPin
     */
    public function setImageUrl($imageUrl = null)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * Get imageUrl.
     *
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }
}
