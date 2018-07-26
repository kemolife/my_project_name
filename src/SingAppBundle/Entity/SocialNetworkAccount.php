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
 *     "facebook"="FacebookAccount",
 *     "yelp"="YelpAccount",
 *     "foursquare"="FoursquareAccount",
 *     "pinterest" = "PinterestAccount",
 *     "bing" = "BingAccount",
 *     "wordofmouth"  ="WordofmouthAccount"
 * })
 * @ORM\Entity()
 */
abstract class SocialNetworkAccount implements HasOwnerInterface, HasBusinessrInterface
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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

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

    /**
     * Set user
     *
     * @param User $user
     *
     * @return SocialNetworkAccount
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
