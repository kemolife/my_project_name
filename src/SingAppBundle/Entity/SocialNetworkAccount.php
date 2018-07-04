<?php


namespace SingAppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SocialNetworkAccount.
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "google"="GoogleAccount",
 *     "instagram"="InstagramAccount",
 *     "facebook"="FacebookAccount"
 * })
 * @ORM\Entity()
 */
abstract class SocialNetworkAccount
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
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\ManyToOne(targetEntity="BusinessInfo")
     * @ORM\JoinColumn(name="business_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $business;

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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return SocialNetworkAccount
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param BusinessInfo $business
     * @return $this
     */
    public function setBusiness(BusinessInfo $business = null)
    {
        $this->business = $business;

        return $this;
    }

    /**
     * Get business
     *
     * @return BusinessInfo
     */
    public function getBusiness()
    {
        return $this->business;
    }
}
