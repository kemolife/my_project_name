<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\HasOwnerInterface;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Media.
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MediaRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\MediaEntityListener"
 *     })
 */
class Media implements HasOwnerInterface
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
     * @ORM\Column(type="string", length=254, nullable=false)
     * @Assert\NotBlank()
     */
    protected $path;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $isUsed = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="photos", cascade={"persist"})
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $post;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path.l
     *
     * @param string $path
     *
     * @return Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set user.
     *
     * @param User $user
     *
     * @return Media
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
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
     * Set isUsed.
     *
     * @param int $isUsed
     *
     * @return Media
     */
    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;

        return $this;
    }

    /**
     * Get isUsed.
     *
     * @return int
     */
    public function getIsUsed()
    {
        return $this->isUsed;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime $creationDate
     *
     * @return Media
     * @ORM\PrePersist()
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = new \DateTime('now');

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set post
     *
     * @param InstagramPost $post
     *
     * @return Media
     */
    public function setPost(InstagramPost $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return InstagramPost
     */
    public function getPost()
    {
        return $this->post;
    }
}
