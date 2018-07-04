<?php


namespace SingAppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Post.
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "google"="GooglePost",
 *     "instagram"="InstagramPost"
 * })
 * @ORM\Entity()
 */
abstract class Post
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
     * @ORM\OneToMany(targetEntity="Images", mappedBy="post", cascade={"persist"})
     */
    protected $photos;

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
     * Constructor
     */
    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Add photo
     *
     * @param Images $photo
     *
     * @return Post
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
}
