<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Photo.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\FileUploadListener"
 *     })
 */
class PinterestPhoto extends Images
{
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
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="photos")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    protected $post;

    /**
     * Set post
     *
     * @param \SingAppBundle\Entity\PinterestPin|null $post
     *
     * @return PinterestPhoto
     */
    public function setPost(\SingAppBundle\Entity\PinterestPin $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \SingAppBundle\Entity\PinterestPin|null
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set isUsed.
     *
     * @param int $isUsed
     *
     * @return PinterestPhoto
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
     * @return PinterestPhoto
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

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
     * Set user.
     *
     * @param \SingAppBundle\Entity\User|null $user
     *
     * @return PinterestPhoto
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
}
