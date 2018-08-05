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
class TruelocalAccount extends SocialNetworkAccount
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
    protected $businessId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $profile;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $profileUrl;

    /**
     * Set user
     *
     * @param User $user
     *
     * @return TruelocalAccount
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
     * @return TruelocalAccount
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
     * @return TruelocalAccount
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
     * Set businessId.
     *
     * @param string|null $businessId
     *
     * @return TruelocalAccount
     */
    public function setBusinessId($businessId = null)
    {
        $this->businessId = $businessId;

        return $this;
    }

    /**
     * Get businessId.
     *
     * @return string|null
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * Set profile.
     *
     * @param string|null $profile
     *
     * @return TruelocalAccount
     */
    public function setProfile($profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile.
     *
     * @return string|null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set profileUrl.
     *
     * @param string|null $profileUrl
     *
     * @return TruelocalAccount
     */
    public function setProfileUrl($profileUrl = null)
    {
        $this->profileUrl = $profileUrl;

        return $this;
    }

    /**
     * Get profileUrl.
     *
     * @return string|null
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }
}
