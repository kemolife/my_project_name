<?php

namespace SingAppBundle\Twig;


use SingAppBundle\Providers\InstagramBusiness;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetInstagramComments extends \Twig_Extension
{
    /**
     * @var InstagramBusiness $instagramService
     */
    private $instagramService;

    public function __construct(ContainerInterface $container)
    {
        $this->instagramService = $container->get('app.anstagram.service');
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getComments', array($this, 'getComments')),
            new \Twig_SimpleFunction('getComment', array($this, 'getComment')),
        );
    }

    public function getAllComments()
    {
        return $this->instagramService->getAllComments();
    }

    public function getComments($mediaId, $commentsId)
    {
        return $this->instagramService->getCommentReplies($mediaId, $commentsId);
    }

    public function getComment($mediaId, $commentsId)
    {
        return $this->instagramService->getCommentReplies($mediaId, $commentsId)->getParentComment();
    }
}