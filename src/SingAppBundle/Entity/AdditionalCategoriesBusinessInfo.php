<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdditionalCategoriesBusinessInfo
 *
 * @ORM\Table(name="additional_categories_business_info")
 * @ORM\Entity(repositoryClass="SingAppBundle\Repository\AdditionalCategoriesBusinessInfoRepository")
 */
class AdditionalCategoriesBusinessInfo
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
     * @var string
     *
     * @ORM\Column(name="category_id", type="string", length=255)
     */
    private $categoryId;


    /**
     * @ORM\ManyToMany(targetEntity="BusinessInfo", mappedBy="additionalCategories")
     **/
    private $business;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->business = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return AdditionalCategoriesBusinessInfo
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
     * Add business.
     *
     * @param \SingAppBundle\Entity\BusinessInfo $business
     *
     * @return AdditionalCategoriesBusinessInfo
     */
    public function addBusiness(\SingAppBundle\Entity\BusinessInfo $business)
    {
        $this->business[] = $business;

        return $this;
    }

    /**
     * Remove business.
     *
     * @param \SingAppBundle\Entity\BusinessInfo $business
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeBusiness(\SingAppBundle\Entity\BusinessInfo $business)
    {
        return $this->business->removeElement($business);
    }

    /**
     * Get business.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBusiness()
    {
        return $this->business;
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Set categoryId.
     *
     * @param string $categoryId
     *
     * @return AdditionalCategoriesBusinessInfo
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId.
     *
     * @return string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }
}
