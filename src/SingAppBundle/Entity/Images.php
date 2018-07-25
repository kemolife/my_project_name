<?php


namespace SingAppBundle\Entity;

use SingAppBundle\Entity\Interfaces\Base64UploadInterface;
use SingAppBundle\Entity\Traits\Base64UploadTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Images.
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "business"="BusinessImage",
 *     "instagram" = "InstagramPhoto",
 *     "google" = "GooglePhoto",
 *     "pinterest" = "PinterestPhoto"
 * })
 * @ORM\Entity()
 */
abstract class Images
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
     * @ORM\Column(type="string", nullable=false)
     * @Assert\File(
     *     mimeTypes = {"image/png", "image/jpeg", "image/gif"}
     * )
     * @Assert\NotBlank(groups={"create"})
     */
    protected $image;

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
     * Set image
     *
     * @param string $image
     *
     * @return Images
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

}
