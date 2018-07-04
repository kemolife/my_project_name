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
class GooglePhoto extends Images
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
     * @param \SingAppBundle\Entity\GooglePost|null $post
     *
     * @return GooglePhoto
     */
    public function setPost(\SingAppBundle\Entity\GooglePost $post = null)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return \SingAppBundle\Entity\GooglePost|null
     */
    public function getPost()
    {
        return $this->post;
    }
}