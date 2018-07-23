<?php

namespace SingAppBundle\Entity;


interface HasBusinessrInterface
{
    /**
     * @return BusinessInfo
     */
    public function getBusiness();

    /**
     * @param BusinessInfo $business
     * @return mixed
     */
    public function setBusiness(BusinessInfo $business);
}
