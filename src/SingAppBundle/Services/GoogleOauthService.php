<?php

namespace SingAppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;

class GoogleOauthService
{
    public $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


}