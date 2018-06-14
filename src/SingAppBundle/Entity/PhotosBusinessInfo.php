<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhotosBusinessInfo
 *
 * @ORM\Table(name="photos_business_info")
 * @ORM\Entity(repositoryClass="SingAppBundle\Repository\PhotosBusinessInfoRepository")
 */
class PhotosBusinessInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="business_id", type="integer")
     */
    private $businessId;

    /**
     * Many Features have One Product.
     * @ORM\ManyToOne(targetEntity="BusinessInfo", inversedBy="additionalCategories")
     * @ORM\JoinColumn(name="business_id", referencedColumnName="id")
     */
    private $business;


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
     * Set name.
     *
     * @param string $name
     *
     * @return PhotosBusinessInfo
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set businessId.
     *
     * @param int $businessId
     *
     * @return PhotosBusinessInfo
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;

        return $this;
    }

    /**
     * Get businessId.
     *
     * @return int
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }
}
