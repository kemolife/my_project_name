<?php

namespace SingAppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * WordofmouthAccount.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\SetBusinessListener"
 *     })
 */
class WordofmouthAccount extends SocialNetworkAccount
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
     * Set user
     *
     * @param User $user
     *
     * @return WordofmouthAccount
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
     * @return WordofmouthAccount
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
     * @return WordofmouthAccount
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
}
