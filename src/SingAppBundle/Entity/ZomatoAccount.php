<?php

namespace SingAppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * YelpAccount.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\SetBusinessListener"
 *     })
 */
class ZomatoAccount extends SocialNetworkAccount
{

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $userEmail;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $userPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $userServiceId;

    /**
     * Set user
     *
     * @param User $user
     *
     * @return ZomatoAccount
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
    /**
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
    }

    /**
     * Set userEmail.
     *
     * @param string $userEmail
     *
     * @return ZomatoAccount
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    /**
     * Get userEmail.
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * Set userPassword.
     *
     * @param string $userPassword
     *
     * @return ZomatoAccount
     */
    public function setUserPassword($userPassword)
    {
        $this->userPassword = $userPassword;

        return $this;
    }

    /**
     * Get userPassword.
     *
     * @return string
     */
    public function getUserPassword()
    {
        return $this->userPassword;
    }

    /**
     * Set userServiceId.
     *
     * @param string|null $userServiceId
     *
     * @return ZomatoAccount
     */
    public function setUserServiceId($userServiceId = null)
    {
        $this->userServiceId = $userServiceId;

        return $this;
    }

    /**
     * Get userServiceId.
     *
     * @return string|null
     */
    public function getUserServiceId()
    {
        return $this->userServiceId;
    }
}
