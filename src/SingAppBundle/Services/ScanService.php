<?php


namespace SingAppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;

class ScanService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getName()
    {
        $repository = $this->em->getRepository('SingAppBundle:BusinessInfo');

        return $repository->findOneBy(['id' => 1]);
    }
}