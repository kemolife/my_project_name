<?php

namespace SingAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User.
 *
 * @ORM\Entity(repositoryClass="SingAppBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("email", groups={"registration", "default"})
 * @UniqueEntity("username", groups={"registration", "default"})
 * @ORM\EntityListeners({"SingAppBundle\EntityListener\UserEntityListener"}
 * )
 */
class User implements UserInterface
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
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Length(max="255")
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255")
     */
    protected $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\Email()
     * @Assert\NotBlank()
     * @Assert\Length(max="255")
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $password;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min = 5)
     */
    protected $plainPassword = null;

    /**
     * @ORM\Column(type="string", nullable=true, options={"default" : "ROLE_USER"})
     */
    protected $role;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $salt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    protected $registrationDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $listings;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $reviews;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $seoRank;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $messages;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $socialPosts;

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return array($this->role ? $this->role : 'ROLE_USER');
    }

    /**
     * @return User[]
     */
    public function getOwners()
    {
        return array(
            $this,
        );
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set fullName.
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set role.
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Set registrationDate
     *
     * @ORM\PrePersist()
     *
     * @return User
     */
    public function setRegistrationDate($registrationDate = null)
    {
        $this->registrationDate = new \DateTime('now');

        return $this;
    }

    /**
     * Get registrationDate
     *
     * @return \DateTime
     */
    public function getRegistrationDate()
    {
        return $this->registrationDate;
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
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
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
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Set plain password recieved from user input.
     *
     * @param string $plainPassword
     *
     * @return User
     */
    final public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        // this is used to trigger doctirne's update event.
        $this->password = null;

        return $this;
    }

    /**
     * Get plain password.
     *
     * @return string|null
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set listings
     *
     * @param integer $listings
     *
     * @return User
     */
    public function setListings($listings)
    {
        $this->listings = $listings;

        return $this;
    }

    /**
     * Get listings
     *
     * @return integer
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Set reviews
     *
     * @param integer $reviews
     *
     * @return User
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;

        return $this;
    }

    /**
     * Get reviews
     *
     * @return integer
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Set seoRank
     *
     * @param integer $seoRank
     *
     * @return User
     */
    public function setSeoRank($seoRank)
    {
        $this->seoRank = $seoRank;

        return $this;
    }

    /**
     * Get seoRank
     *
     * @return integer
     */
    public function getSeoRank()
    {
        return $this->seoRank;
    }

    /**
     * Set messages
     *
     * @param integer $messages
     *
     * @return User
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get messages
     *
     * @return integer
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set socialPosts
     *
     * @param integer $socialPosts
     *
     * @return User
     */
    public function setSocialPosts($socialPosts)
    {
        $this->socialPosts = $socialPosts;

        return $this;
    }

    /**
     * Get socialPosts
     *
     * @return integer
     */
    public function getSocialPosts()
    {
        return $this->socialPosts;
    }
}
