<?php


namespace SingAppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Post.
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "google"="GooglePost",
 *     "instagram"="InstagramPost",
 *     "facebook"="FacebookPost",
 *     "pinterest" = "PinterestPin",
 *     "youtube" = "YoutubePost",
 *     "linkedin" = "LinkedinPost"
 * })
 * @ORM\Entity()
 */
abstract class Post implements HasBusinessrInterface
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="text", length=1500, nullable=true)
     * @Assert\Length(max="1500")
     * @Assert\NotBlank()
     */
    protected $caption;

    /**
     * @ORM\OneToMany(targetEntity="Media", mappedBy="post", cascade={"persist"})
     */
    protected $media;

    /**
     * @ORM\Column(type="enum_post_status_type", length=255, nullable=true)
     *
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $postDate;

    /**
     * @ORM\Column(type="string")
     */
    protected $socialNetwork;

    /**
     * @var ArrayCollection
     */
    protected $uploadedFiles;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="BusinessInfo")
     * @ORM\JoinColumn(name="business_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $business;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $schedule = 0;

    /**
     * @ORM\Column(type="integer")
     */
    protected $timezoneOffset = 0;

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
     * @return Post
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
     * @return Post
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
     * @return Post
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
     * @return Post
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

    public function setUploadedFiles($uploadedFiles)
    {
        $this->uploadedFiles = $uploadedFiles;

        return $this;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Set socialNetwork
     *
     * @param string $socialNetwork
     *
     * @return Post
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
     * @return Post
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
     * Set user.
     *
     * @param \SingAppBundle\Entity\User|null $user
     *
     * @return Post
     */
    public function setUser(\SingAppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \SingAppBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set business.
     *
     * @param \SingAppBundle\Entity\BusinessInfo|null $business
     *
     * @return Post
     */
    public function setBusiness(\SingAppBundle\Entity\BusinessInfo $business = null)
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Get business.
     *
     * @return \SingAppBundle\Entity\BusinessInfo|null
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * Set timezoneOffset.
     *
     * @param int $timezoneOffset
     *
     * @return Post
     */
    public function setTimezoneOffset($timezoneOffset)
    {
        $this->timezoneOffset = $timezoneOffset;

        return $this;
    }

    /**
     * Get timezoneOffset.
     *
     * @return int
     */
    public function getTimezoneOffset()
    {
        return $this->timezoneOffset;
    }

    /**
     * Add medium.
     *
     * @param \SingAppBundle\Entity\Media $medium
     *
     * @return Post
     */
    public function addMedia(\SingAppBundle\Entity\Media $medium)
    {
        $this->media[] = $medium;

        return $this;
    }

    /**
     * Remove medium.
     *
     * @param \SingAppBundle\Entity\Media $medium
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeMedia(\SingAppBundle\Entity\Media $medium)
    {
        return $this->media->removeElement($medium);
    }

    /**
     * Get media.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedia()
    {
        return $this->media;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->media = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
