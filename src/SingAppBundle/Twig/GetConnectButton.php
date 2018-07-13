<?php


namespace SingAppBundle\Twig;


use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use Symfony\Component\Routing\RouterInterface;

class GetConnectButton extends \Twig_Extension
{

    private $em;
    private $router;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router)
    {
       $this->em = $entityManager;
       $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getButton', array($this, 'getButton')),
        );
    }

    public function getButton($type, BusinessInfo $businessInfo, User $user)
    {
        $button = null;
        $repository = $this->em->getRepository('SingAppBundle:'.ucfirst($type).'Account');

        $siteSettings = $repository->findOneBy(['user' => $user->getId(), 'business' => $businessInfo->getId()]);
        if(null !== $siteSettings){
            $button = '<a href="'.$this->router->generate($type.'-auth').'" class="btn btn-primary"> Connect </a>';
        }

        return $button;
    }

}