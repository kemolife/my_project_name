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
        $this->instagramService = $container->get('instagram_provider');
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getCommentsReplies', array($this, 'getCommentsReplies')),
            new \Twig_SimpleFunction('getComment', array($this, 'getComment')),
        );
    }

    public function getAllComments()
    {
        return $this->instagramService->getAllComments();
    }

    public function getCommentsReplies($mediaId, $commentsId)
    {
        return $this->instagramService->getCommentReplies($mediaId, $commentsId)->getChildComments();
    }

    public function getComment($mediaId, $commentsId)
    {
        return $this->instagramService->getCommentReplies($mediaId, $commentsId)->getParentComment();
    }
}