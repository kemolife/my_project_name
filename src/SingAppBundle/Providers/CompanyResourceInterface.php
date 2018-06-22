<?php


namespace SingAppBundle\Providers;


interface CompanyResourceInterface
{
    public function getResourceCompanies();

    public function updateCompany($id);
}