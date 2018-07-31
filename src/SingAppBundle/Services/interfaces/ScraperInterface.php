<?php

namespace SingAppBundle\Services\interfaces;


interface ScraperInterface
{
    public function saveCookies($prefix, $cookies);
}