<?php

namespace SingAppBundle\Services\interfaces;


use SingAppBundle\Entity\BusinessInfo;

interface CreateServiceAccountInterface
{
    public function createServiceAccount($data, BusinessInfo $business);
}