<?php

namespace SingAppBundle\Entity;


interface HasOwnerInterface
{
    /**
     * @return User[]
     */
    public function getOwners();
}
