<?php

namespace SingAppBundle\Services\interfaces;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;

interface BaseInterface
{
//    public function auth(SocialNetworkAccount $account);

    public function editAccount(SocialNetworkAccount $account, BusinessInfo $business);
}