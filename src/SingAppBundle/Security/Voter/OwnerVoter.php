<?php

namespace SingAppBundle\Security\Voter;

use SingAppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\Container;

class OwnerVoter implements VoterInterface
{
    const ACTION_CREATE = 'CREATE';
    const ACTION_READ = 'READ';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';

    const ABILITY_NAME_OWNER = 'OWNER';

    private $roles;

    public function __construct(ContainerInterface $container)
    {
        $this->roles = $container->getParameter('security.role_hierarchy.roles');
    }

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array(
            self::ACTION_READ,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
            self::ACTION_CREATE,
        ));
    }

    public function supportsClass($class)
    {
        $reflectionClass = new \ReflectionClass($class);

        return $reflectionClass->implementsInterface('SingAppBundle\Entity\HasOwnerInterface');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $role = $user->getRoles();
        if (array_key_exists((string) $role[0], $this->roles)) {
            $roles = $this->roles[(string) $role[0]];
            $roles[] = (string) $role[0];
        } else {
            $roles[] = (string) $role[0];
        }

        if (!$this->supportsClass(get_class($object))) {
            if (get_class($object) == get_class($this)) {
                if (in_array($attributes[0], $roles)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for CREATE, READ, UPDATE or DELETE'
            );
        }

        $reflection = new \ReflectionClass($object);
        $shortClassName = strtoupper(Container::underscore($reflection->getShortName()));
        $baseRoleName = 'ABILITY_'.$shortClassName;

        $attribute = $attributes[0];

        $actionRole = '';
        if (0 === strpos($attribute, $baseRoleName)) {
            $actionRole = substr($attribute, strlen($baseRoleName) + 1);
        }

        if (!$this->supportsAttribute($actionRole)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $roleOwn = $baseRoleName.'_'.self::ABILITY_NAME_OWNER;

        if (in_array($roleOwn, $roles) && in_array($attribute, $roles)) {
            $owners = $object->getOwners();
            foreach ($owners as $owner) {
                if ($owner && $owner->getId() == $user->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

            return VoterInterface::ACCESS_DENIED;
        } elseif (in_array($attribute, $roles)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
