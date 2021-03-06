<?php


namespace SingAppBundle\Twig;


use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class GetConnectButtonAndStatus extends \Twig_Extension
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
            new \Twig_SimpleFunction('getStatusConnect', array($this, 'getStatusConnect')),
            new \Twig_SimpleFunction('getPostButton', array($this, 'getPostButton')),
        );
    }

    public function getButton($type, BusinessInfo $businessInfo, User $user)
    {
        $button = null;
        $repository = $this->em->getRepository('SingAppBundle:'.ucfirst($type).'Account');

        $siteSettings = $repository->findOneBy(['user' => $user->getId(), 'business' => $businessInfo->getId()]);
        if(null === $siteSettings){
            $button = '<a href="'.$this->router->generate($type.'-auth', ['business' => $businessInfo->getId(), 'url' => 'index']).'" class="btn btn-primary"> Connect </a>';
        }
        if(null !== $siteSettings && $type === 'google'){
            $button =  '<a style="margin-right: -30px; float: right;" href="'.$this->router->generate('google-location', ['business' => $businessInfo->getId(), 'url' => 'index']).'" class="btn btn-primary"> Change location </a>';
        }

        return $button;
    }

    public function getPostButton($type, BusinessInfo $businessInfo, User $user)
    {
        $button = null;
        $repository = $this->em->getRepository('SingAppBundle:'.ucfirst($type).'Account');

        $siteSettings = $repository->findOneBy(['user' => $user->getId(), 'business' => $businessInfo->getId()]);
        if(null === $siteSettings){
            $button = '<a href="'.$this->router->generate($type.'-auth', ['business' => $businessInfo->getId(), 'url' => $type.'-post']).'"> <img src="/images/'.$type.'-button-connect.png"
                                                         alt="'.$type.' connect" height="50"></a>';
        }

        return $button;
    }

    public function getStatusConnect($type, BusinessInfo $businessInfo, User $user)
    {
        $status = '<div class="buttons"><div class="synced"><div class="btn-xs"><i class="fas fa-check-circle synce-done"></i> <span>Connected</span></div></div></div>';
        $repository = $this->em->getRepository('SingAppBundle:'.ucfirst($type).'Account');

        $siteSettings = $repository->findOneBy(['user' => $user->getId(), 'business' => $businessInfo->getId()]);
        if(null === $siteSettings){
            $status = ' <div class="buttons"><div class="synced"><div class="btn-xs"><i class="fas fa-circle-notch fa-spin sync-in-progress"></i><span>Listing sync in progress</span></div> </div></div>';
        }

        return $status;
    }

}