<?php

namespace SingAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * PinterestPin.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({
 *     "SingAppBundle\EntityListener\SetOwnerListener",
 *     "SingAppBundle\EntityListener\PinterestPinEntityListener"})
 */
class PinterestPin  extends Post implements HasOwnerInterface
{


    /**
     * @ORM\ManyToOne(targetEntity="SocialNetworkAccount")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $board;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $link;


    /**
     * @return User[]
     */
    public function getOwners()
    {
        return [$this->getUser()];
    }

    /**
     * Set board.
     *
     * @param string $board
     *
     * @return PinterestPin
     */
    public function setBoard($board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board.
     *
     * @return string
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set link.
     *
     * @param string|null $link
     *
     * @return PinterestPin
     */
    public function setLink($link = null)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link.
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set account.
     *
     * @param \SingAppBundle\Entity\SocialNetworkAccount|null $account
     *
     * @return PinterestPin
     */
    public function setAccount(\SingAppBundle\Entity\SocialNetworkAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \SingAppBundle\Entity\SocialNetworkAccount|null
     */
    public function getAccount()
    {
        return $this->account;
    }
}
