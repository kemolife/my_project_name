<?php

namespace SingAppBundle\Security\Authentication;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppUserProvider implements UserProviderInterface
{
    /** @var EntityManager $em */
    protected $em;

    /**
     * ApiKeyUserProvider constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $username
     *
     * @return null|object
     */
    public function loadUserByUsername($credentials)
    {
        $repository = $this->em->getRepository('SingAppBundle:User');
        $user = $repository->findOneBy(['email' => $credentials['username']]);


        if (!$user instanceof User) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            $message = sprintf('Unsupported class type : %s', $class);
            throw new UnsupportedUserException($message);
        }
        $user = $this->em->getRepository('SingAppBundle:User')->findOneBy(['email' => $user->getEmail()]);

        return $user;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'SingAppBundle\Entity\User' === $class;
    }
}
