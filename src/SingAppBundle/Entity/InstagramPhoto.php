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
class InstagramPhoto extends Images
{

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="photos")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    protected $post;

    /**
     * Set post
     *
     * @param \SingAppBundle\Entity\InstagramPost|null $post
     *
     * @return InstagramPhoto
     */
    public function setPost(\SingAppBundle\Entity\InstagramPost $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \SingAppBundle\Entity\InstagramPost|null
     */
    public function getPost()
    {
        return $this->post;
    }
}
