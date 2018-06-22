<?php

namespace SingAppBundle\Providers;


use Curl\Curl;
use Symfony\Component\HttpFoundation\Session\Session;

class FacebookCompanies implements CompanyResourceInterface
{
    protected $companyUpdateUrl;
    protected $companiesUrl;
    protected $companyAccessToken;
    protected $session;
    protected $curl;
    protected $dataCompany;

    /**
     * FacebookCompanies constructor.
     * @param Session $session
     * @throws OAuthCompanyException
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        try {
            $this->curl = new Curl();
        }catch (\ErrorException $e){
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    protected function getClientAccessToken()
    {
        return $this->session->get('facebook')['token'];
    }

    public function getCompaniesUrl()
    {
        $userId = $this->session->get('facebook')['userId'];
        return $this->setAccessTokenToUrl('https://graph.facebook.com/v3.0/'.$userId.'/accounts');
    }

    public function setAccessTokenToUrl($url)
    {
        return $url.'?access_token='.$this->getClientAccessToken();
    }

    public function setCompanyAccessTokenToUrl($url)
    {
        return $url.'?access_token='.$this->getCompanyAccessToken();
    }

    public function getCompanyUpdateUrl()
    {
        return $this->companyUpdateUrl;
    }

    public function setCompanyUpdateUrl($pageId)
    {
        $this->companyUpdateUrl = $this->setCompanyAccessTokenToUrl('https://graph.facebook.com/v3.0/'.$pageId);
    }

    public function setCompanyAccessToken($token)
    {
        $this->companyAccessToken = $token;
    }

    public function getCompanyAccessToken()
    {
        return $this->companyAccessToken;
    }

    /**
     * @throws OAuthCompanyException
     */
    public function getResourceCompanies()
    {
        $this->curl->get($this->getCompanyUpdateUrl());
        $this->curl->close();
        if ($this->curl->error) {
            throw new OAuthCompanyException($this->curl->error);
        } else {
            $companies = json_decode($this->curl->response);
            foreach ($companies as $company) {
                $this->setCompanyAccessToken($company->access_token);
                $this->updateCompany($company->id);
            }
        }
    }

    /**
     * @param $id
     * @throws OAuthCompanyException
     */
    public function updateCompany($id)
    {
        $this->setCompanyUpdateUrl($id);
        $this->curl->post($this->getCompanyUpdateUrl(), $this->dataCompany);
        $this->curl->close();
        if ($this->curl->error) {
            throw new OAuthCompanyException($this->curl->error);
        } else {

        }
    }
}