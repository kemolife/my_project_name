<?php

namespace SingAppBundle\EntityListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserEntityListener
{
    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    /**
     * UserInterfaceEntityListener constructor.
     *
     * @param UserPasswordEncoder $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder, TokenStorageInterface $tokenStorage)
    {
        $this->encoder = $encoder;

        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Encode password when peresisting new user.
     *
     * @param UserInterface $user
     * @param LifecycleEventArgs $args
     *
     * @ORM\PrePersist
     */
    public function encodePasswordOnPrePersist(UserInterface $user, LifecycleEventArgs $args)
    {
        $this->encodePassword($user);
    }


    /**
     * Edit user role.
     *
     * @param UserInterface $user
     * @param PreUpdateEventArgs $args
     *
     * @ORM\PreUpdate
     */
    public function editRole(UserInterface $user, PreUpdateEventArgs $args)
    {
        $changes = $args->getEntityChangeSet();
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
        } else {
            return;
        }

        if (array_key_exists('role', $changes) && 'ROLE_SUPER_ADMIN' != $user->getRole()) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Encode new password when updating a user.
     *
     * @param UserInterface $user
     * @param PreUpdateEventArgs $args
     *
     * @ORM\PreUpdate
     */
    public function encodePasswordOnPreUpdate(UserInterface $user, LifecycleEventArgs $args)
    {
        $this->encodePassword($user);

        $om = $args->getObjectManager();
        $uow = $om->getUnitOfWork();
        $meta = $om->getClassMetadata(get_class($user));

        $uow->recomputeSingleEntityChangeSet($meta, $user);
    }

    /**
     * Encode the value of plainPassword property to password property.
     *
     * @param UserInterface $user
     */
    private function encodePassword(UserInterface $user)
    {
        if (null === $plainPassword = $user->getPlainPassword()) {
            return;
        }

        $encoded = $this->encoder->encodePassword($user, $plainPassword);

        $user->setPassword($encoded);
    }
}
