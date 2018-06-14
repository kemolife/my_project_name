<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessInfo
 *
 * @ORM\Table(name="business_info")
 * @ORM\Entity(repositoryClass="SingAppBundle\Repository\BusinessInfoRepository")
 */
class BusinessInfo
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=255)
     */
    private $category;

    /**
     * @var string|null
     *
     * @ORM\Column(name="additional_categories", type="integer", nullable=true)
     * @ORM\OneToMany(targetEntity="AdditionalCategoriesBusinessInfo", mappedBy="business")
     * @ORM\JoinColumn(nullable=true)
     */
    private $additionalCategories;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var int
     *
     * @ORM\Column(name="phone_number", type="integer")
     */
    private $phoneNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="website", type="string", length=100, nullable=true)
     */
    private $website;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="opening_hours", type="time", nullable=true)
     */
    private $openingHours;

    /**
     * @var string|null
     *
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @var string|null
     *
     * @ORM\Column(name="payment_options", type="string", length=255, nullable=true)
     */
    private $paymentOptions;

    /**
     * @var string|null
     *
     * @ORM\Column(name="video", type="string", length=255, nullable=true)
     */
    private $video;

    /**
     * @var int|null
     *
     * @ORM\Column(name="photos", type="integer", nullable=true)
     * @ORM\OneToMany(targetEntity="PhotosBusinessInfo", mappedBy="business")
     */
    private $photos;

    public function __construct() {
        $this->additionalCategories = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }


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
     * @return BusinessInfo
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
     * Set category.
     *
     * @param string $category
     *
     * @return BusinessInfo
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set additionalCategories.
     *
     * @param string|null $additionalCategories
     *
     * @return BusinessInfo
     */
    public function setAdditionalCategory($additionalCategories = null)
    {
        $this->additionalCategories = $additionalCategories;

        return $this;
    }

    /**
     * Get additionalCategories.
     *
     * @return string|null
     */
    public function getAdditionalCategory()
    {
        return $this->additionalCategories;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return BusinessInfo
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phoneNumber.
     *
     * @param int $phoneNumber
     *
     * @return BusinessInfo
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber.
     *
     * @return int
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set website.
     *
     * @param string|null $website
     *
     * @return BusinessInfo
     */
    public function setWebsite($website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string|null
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return BusinessInfo
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set openingHours.
     *
     * @param \DateTime|null $openingHours
     *
     * @return BusinessInfo
     */
    public function setOpeningHours($openingHours = null)
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    /**
     * Get openingHours.
     *
     * @return \DateTime|null
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * Set logo.
     *
     * @param string|null $logo
     *
     * @return BusinessInfo
     */
    public function setLogo($logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo.
     *
     * @return string|null
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set paymentOptions.
     *
     * @param string|null $paymentOptions
     *
     * @return BusinessInfo
     */
    public function setPaymentOptions($paymentOptions = null)
    {
        $this->paymentOptions = $paymentOptions;

        return $this;
    }

    /**
     * Get paymentOptions.
     *
     * @return string|null
     */
    public function getPaymentOptions()
    {
        return $this->paymentOptions;
    }

    /**
     * Set video.
     *
     * @param string|null $video
     *
     * @return BusinessInfo
     */
    public function setVideo($video = null)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video.
     *
     * @return string|null
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set photos.
     *
     * @param int|null $photos
     *
     * @return BusinessInfo
     */
    public function setPhotos($photos = null)
    {
        $this->photos = $photos;

        return $this;
    }

    /**
     * Get photos.
     *
     * @return int|null
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}
