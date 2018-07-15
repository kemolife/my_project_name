<?php

namespace SingAppBundle\Twig;


class ClassTwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'class' => new \Twig_SimpleFunction('class', array($this, 'getClass'))
        );
    }

    public function getName()
    {
        return 'class_twig_extension';
    }

    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}