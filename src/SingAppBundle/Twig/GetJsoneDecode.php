<?php

namespace SingAppBundle\Twig;


use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Services\PinterestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GetJsoneDecode extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('jsonDecode', array($this, 'jsonDecode'))
        );
    }

    public function jsonDecode($str) {
        if(null === $str){
            $str = '{"days":{"Monday":{"type":"open","slots":[{"start":"07:30am"},{"end":"07:30pm"}]},"Tuesday":{"type":"open","slots":[{"start":"07:30am"},{"end":"07:30pm"}]},"Wednesday":{"type":"open","slots":[{"start":"07:30am"},{"end":"07:30pm"}]},"Thursday":{"type":"open","slots":[{"start":"07:30am"},{"end":"07:30pm"}]},"Friday":{"type":"open","slots":[{"start":"07:30am"},{"end":"07:30pm"}]},"Saturday":{"type":"open","slots":[{"start":"07:30am"},{"end":"02:00pm"}]},"Sunday":{"type":"closed"}}}';
        }
        $array = json_decode($str, true);
        return $array['days'];
    }
}