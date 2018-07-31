<?php

namespace SingAppBundle\Services\interfaces;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;

interface BaseInterface
{
    public function auth(SocialNetworkAccount $account);

    public function createAccount(SocialNetworkAccount $account, $data);

    public function getAccount(User $user, BusinessInfo $business);

    public function editAccount(SocialNetworkAccount $account, BusinessInfo $business);
}