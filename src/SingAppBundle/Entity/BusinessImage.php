<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BusinessImage.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\FileUploadListener"
 * }
 * )
 */
class BusinessImage extends Images
{
    /**
     * @ORM\ManyToOne(targetEntity="BusinessInfo", inversedBy="photos")
     * @ORM\JoinColumn(name="business_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    protected $businessInfo;

    /**
     * Set businessInfo.
     *
     * @param \SingAppBundle\Entity\BusinessInfo|null $businessInfo
     *
     * @return BusinessImage
     */
    public function setBusinessInfo(\SingAppBundle\Entity\BusinessInfo $businessInfo = null)
    {
        $this->businessInfo = $businessInfo;

        return $this;
    }

    /**
     * Get businessInfo.
     *
     * @return \SingAppBundle\Entity\BusinessInfo|null
     */
    public function getBusinessInfo()
    {
        return $this->businessInfo;
    }
}
