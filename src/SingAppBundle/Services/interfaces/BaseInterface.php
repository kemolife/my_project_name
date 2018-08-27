<?php

namespace SingAppBundle\Services\interfaces;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;

interface BaseInterface
{
    const STATUS_FALSE = false;
    const STATUS_TRUE = true;

//    public function auth(SocialNetworkAccount $account);

    public function editAccount(SocialNetworkAccount $account, BusinessInfo $business);

    public function searchBusiness($account, BusinessInfo $business);
}