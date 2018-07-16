<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * BusinessInfo
 *
 * @ORM\Table(name="business_info")
 * @ORM\Entity(repositoryClass="SingAppBundle\Repository\BusinessInfoRepository")
 * @ORM\EntityListeners({"SingAppBundle\EntityListener\BusinessInfoEntityListener"})
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var int
     *
     * @ORM\Column(name="phone_number", type="string", length=100)
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
     * @var string|null
     *
     * @ORM\Column(name="opening_hours", type="text", nullable=true)
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

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
     * @var string|null
     * @ORM\ManyToMany(targetEntity="AdditionalCategoriesBusinessInfo", inversedBy="business", cascade={"persist"})
     * @ORM\JoinTable(
     *  name="addition_category_business",
     *  joinColumns={
     *      @ORM\JoinColumn(name="bus_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     *  }
     * )
     */
    private $additionalCategories;

    /**
     * @var File|null
     *
     * @ORM\OneToMany(targetEntity="BusinessImage", mappedBy="businessInfo", cascade={"persist"})
     *
     */
    private $photos;

    /**
     * @var ArrayCollection
     */
    protected $uploadedFiles;

    /**
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->additionalCategories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string $phoneNumber
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
     * @return string
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
     * Add photo.
     *
     * @param \SingAppBundle\Entity\BusinessImage $photo
     *
     * @return BusinessInfo
     */
    public function addPhoto(\SingAppBundle\Entity\BusinessImage $photo)
    {
        $this->photos[] = $photo;

        return $this;
    }

    /**
     * Remove photo.
     *
     * @param \SingAppBundle\Entity\BusinessImage $photo
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePhoto(\SingAppBundle\Entity\BusinessImage $photo)
    {
        return $this->photos->removeElement($photo);
    }

    /**
     * Get photos.
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
     * Set user.
     *
     * @param \SingAppBundle\Entity\User|null $user
     *
     * @return BusinessInfo
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
     * Add additionalCategory.
     *
     * @param \SingAppBundle\Entity\AdditionalCategoriesBusinessInfo $additionalCategory
     *
     * @return BusinessInfo
     */
    public function addAdditionalCategory(\SingAppBundle\Entity\AdditionalCategoriesBusinessInfo $additionalCategory)
    {
        $this->additionalCategories[] = $additionalCategory;

        return $this;
    }

    /**
     * Remove additionalCategory.
     *
     * @param \SingAppBundle\Entity\AdditionalCategoriesBusinessInfo $additionalCategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAdditionalCategory(\SingAppBundle\Entity\AdditionalCategoriesBusinessInfo $additionalCategory)
    {
        return $this->additionalCategories->removeElement($additionalCategory);
    }

    /**
     * Get additionalCategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdditionalCategories()
    {
        return $this->additionalCategories;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return BusinessInfo
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
