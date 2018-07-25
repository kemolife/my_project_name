<?php

namespace SingAppBundle\Twig;


use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Services\PinterestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClearQueryForError extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('clearQuery', array($this, 'clearQuery'))
        );
    }

    public function clearQuery($array)
    {
        if(array_key_exists('error', $array)){
            unset($array['error']);
        }
        return $array;
    }
}