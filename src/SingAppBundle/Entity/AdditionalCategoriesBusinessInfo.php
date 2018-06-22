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
     * @ORM\ManyToOne(targetEntity="BusinessInfo", inversedBy="additionalCategories", cascade={"persist"})
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     **/
    private $business;

    /**
     * @var string|null
     */

    private $nameCollection;


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
     * Set business.
     *
     * @param \SingAppBundle\Entity\BusinessInfo|null $business
     *
     * @return AdditionalCategoriesBusinessInfo
     */
    public function setBusiness(\SingAppBundle\Entity\BusinessInfo $business = null)
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Get business.
     *
     * @return \SingAppBundle\Entity\BusinessInfo|null
     */
    public function getBusiness()
    {
        return $this->business;
    }
}
