<?php

namespace SingAppBundle\Providers;


use Curl\Curl;
use Symfony\Component\HttpFoundation\Session\Session;

class GoogleCompanies implements CompanyResourceInterface
{
    protected $companyUpdateUrl;
    protected $companiesUrl;
    protected $accessToken;
    protected $session;
    protected $curl;
    protected $dataCompany;

    /**
     * GoogleCompanies constructor.
     * @param Session $session
     * @param Curl $curl
     * @throws OAuthCompanyException
     */
    public function __construct(Session $session, Curl $curl)
    {
        $this->session = $session;
        try {
            $this->curl = new Curl();
        }catch (\ErrorException $e){
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getCompaniesUrl()
    {
        return 'https://mybusiness.googleapis.com/v3/accounts';
    }

    public function getCompanyUpdateUrl()
    {
        return 'https://mybusiness.googleapis.com/v3/';
    }

    protected function getAccessToken()
    {
        $this->accessToken = $this->session->get('googleAccessToken');
    }

    public function getResourceCompanies()
    {
        $this->curl->get($this->getCompanyUpdateUrl());
        $this->curl->setHeader('Authorization', 'Bearer '.$this->getAccessToken());
        $this->curl->close();
        if ($this->curl->error) {
            throw new OAuthCompanyException($this->curl->error);
        } else {
            $companies = json_decode($this->curl->response);
            foreach ($companies as $company) {
                $this->updateCompany($company->accountName);
            }
        }
    }

    public function updateCompany($name)
    {
        $this->curl->put($this->getCompanyUpdateUrl().$name, $this->dataCompany );
        $this->curl->setHeader('Authorization', 'Bearer '.$this->getAccessToken());
        $this->curl->close();
        if ($this->curl->error) {
            throw new OAuthCompanyException($this->curl->error);
        } else {

        }
    }
}