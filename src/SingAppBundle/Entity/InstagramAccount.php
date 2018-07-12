<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * InstagramAccount.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({"SingAppBundle\EntityListener\SetOwnerListener"})
 */
class InstagramAccount extends SocialNetworkAccount
{
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    protected $login;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $isDefault = 0;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return InstagramAccount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return InstagramAccount
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return InstagramAccount
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set isDefault
     *
     * @param integer $isDefault
     *
     * @return InstagramAccount
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return integer
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

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
     * @return InstagramAccount
     * @ORM\PrePersist()
     */
    public function setCreated($created)
    {
        $this->created = new \DateTime();

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
     * Set business
     *
     * @param BusinessInfo $business
     *
     * @return InstagramAccount
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
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
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

    /**
     * Set user
     *
     * @param User $user
     *
     * @return InstagramAccount
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }
}
