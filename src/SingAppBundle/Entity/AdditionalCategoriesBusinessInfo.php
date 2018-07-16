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
     * @ORM\ManyToMany(targetEntity="BusinessInfo", mappedBy="additionalCategories")
     **/
    private $business;

    /**
     * @ORM\OneToOne(targetEntity="BusinessInfo", mappedBy="category")
     */
    protected $category;

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
     * Set category.
     *
     * @param \SingAppBundle\Entity\BusinessInfo|null $category
     *
     * @return AdditionalCategoriesBusinessInfo
     */
    public function setCategory(\SingAppBundle\Entity\BusinessInfo $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \SingAppBundle\Entity\BusinessInfo|null
     */
    public function getCategory()
    {
        return $this->category;
    }
}
