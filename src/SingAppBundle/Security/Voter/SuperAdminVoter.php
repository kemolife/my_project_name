<?php

namespace SingAppBundle\Security\Voter;

use SingAppBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SuperAdminVoter implements VoterInterface
{
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $explodeRoles = explode('_', $attributes[0]);

        if ('OWNER' == $explodeRoles[count($explodeRoles) - 1]) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $roles = $user->getRoles();
        $explodeRoles = strripos($attributes[0], '_');
        if ('OWNER' == substr($attributes[0], $explodeRoles + 1, strlen($attributes[0]))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }
        if (in_array(self::ROLE_SUPER_ADMIN, $roles)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
