<?php

namespace SingAppBundle\EntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\HasBusinessrInterface;
use SingAppBundle\Entity\HasOwnerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SetBusinessListener
{
    private $session;

    /**
     * SparePartTypeListener constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Set user.
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @param HasBusinessrInterface  $entity
     * @param LifecycleEventArgs $args
     */
    public function setBusiness(HasBusinessrInterface $entity, LifecycleEventArgs $args)
    {
        $businessId = $this->session->get('business');
        $repository = $args->getEntityManager()->getRepository('SingAppBundle:BusinessInfo');
        $business = $repository->findOneById($businessId);
        if($business instanceof BusinessInfo){
            $entity->setBusiness($business);
        }
    }
}
