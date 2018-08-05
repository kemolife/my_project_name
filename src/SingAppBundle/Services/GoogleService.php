<?php

namespace SingAppBundle\Services;


use Curl\Curl;
use Google_Service_Exception;
use Google_Service_MyBusiness_Account;
use Google_Service_MyBusiness_BusinessHours;
use Google_Service_MyBusiness_Category;
use Google_Service_MyBusiness_LatLng;
use Google_Service_MyBusiness_Location;
use Google_Service_MyBusiness_PostalAddress;
use Google_Service_MyBusiness_Profile;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Helper\PHPFunctionsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_MyBusiness;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\HttpFoundation\Request;

class GoogleService
{
    private $em;
    private $domain;

    public function __construct(EntityManagerInterface $entityManager, $domain)
    {
        $this->em = $entityManager;
        $this->domain = $domain;
    }

    public function auth()
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);
        $client->setRedirectUri($this->domain . '/google/oauth2callback');


        return $client->createAuthUrl();
    }

    public function getAccessToken($code)
    {

        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);
        $client->setRedirectUri($this->domain . '/google/oauth2callback');

        return $client->authenticate($code);
    }

    public function refreshAccessToken(GoogleAccount $googleAccount)
    {

        $client = new Google_Client();

        $client->setAuthConfig('client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);
        $client->setRedirectUri($this->domain . '/google/oauth2callback');

        return $client->fetchAccessTokenWithRefreshToken($googleAccount->getRefreshToken());

    }

    public function getAccountsLocations(GoogleAccount $googleAccount)
    {
        $locations = [];

        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessToken($googleAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true); // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

        $googleMyBusiness = new Google_Service_MyBusiness($client);
        try {
            $accounts = $googleMyBusiness->accounts->listAccounts()->getAccounts();
        }catch (Google_Service_Exception $e){
            try {
                $accessTokeData = $this->refreshAccessToken($googleAccount);
                $this->createUpdateGoogleAccount($accessTokeData, $googleAccount);
                $this->getAccountsLocations($googleAccount);
            } catch (\Exception $e) {
                throw new OAuthCompanyException(json_encode($e->getMessage()));
            }
        }
        /**
         * @var Google_Service_MyBusiness_Account $account
         */
        foreach ($accounts as $account) {
            $locations[$account->getName()] = ['locations' => $googleMyBusiness->accounts_locations->listAccountsLocations($account->getName())->getLocations(), 'info' => $account];
        }

        return $locations;
    }

    private function getPostBody(BusinessInfo $businessInfo)
    {
        $postBody = new Google_Service_MyBusiness_Location();
        $postBody->setLocationName($businessInfo->getName());
        $postBody->setPrimaryPhone($businessInfo->getPhoneNumber());
        $postBody->setLanguageCode('en');
        $postBody->setWebsiteUrl($businessInfo->getWebsite());

        $profile = new Google_Service_MyBusiness_Profile();
        $profile->setDescription($businessInfo->getDescription());
        $postBody->setProfile($profile);
        $postBody->setWebsiteUrl($businessInfo->getWebsite());

        $address = new Google_Service_MyBusiness_PostalAddress();
        $address->setRegionCode($businessInfo->getRegionCode());
        $address->setAddressLines($businessInfo->getAddress());
        $address->setAdministrativeArea($businessInfo->getAdministrativeArea());
        $address->setLocality($businessInfo->getLocality());
        $address->setPostalCode($businessInfo->getPostalCode());
        $address->setLanguageCode('en');
        $postBody->setAddress($address);

        $latLng = new Google_Service_MyBusiness_LatLng();
        $latLng->setLatitude($businessInfo->getLatitude());
        $latLng->setLongitude($businessInfo->getLongitude());
        $postBody->setLatlng($latLng);

        $primaryCategory = new Google_Service_MyBusiness_Category();
        $primaryCategory->setDisplayName($businessInfo->getCategory()->getName());
        $primaryCategory->setCategoryId($businessInfo->getCategory()->getCategoryId());
        $postBody->setPrimaryCategory($primaryCategory);
        $additionalCategory = [];
        foreach ($businessInfo->getAdditionalCategories() as  $item){
            $category = new Google_Service_MyBusiness_Category();
            $category->setDisplayName($item->getName());
            $category->setCategoryId($item->getCategoryId());
            array_push($additionalCategory, $category);
        }
        $postBody->setAdditionalCategories($additionalCategory);

        $regularHours = new Google_Service_MyBusiness_BusinessHours();
        $period = [];
        foreach (\GuzzleHttp\json_decode($businessInfo->getOpeningHours())->days as $key => $item) {
            if ($item->type === 'open') {
                $day = new \stdClass();
                $day->openDay = strtoupper($key);
                $day->openTime = $item->slots[0]->start;
                $day->closeDay = strtoupper($key);
                $day->closeTime = $item->slots[1]->end;
                array_push($period, $day);
            }
        }
        $regularHours->setPeriods($period);
        $postBody->setRegularHours($regularHours);

        return $postBody;
    }

    private function curlSender($url, $postBody, GoogleAccount $googleAccount, $method)
    {
        $curl = new Curl();
        $curl->setHeaders([
            'Authorization' => 'Bearer ' . $googleAccount->getAccessToken(),
            'Content-Type' => 'application/json'
        ]);
        $curl->$method($url, $postBody);

        if (isset($curl->response->error)) {
            if ($curl->response->error->code === 401) {
                try {
                    $accessTokeData = $this->refreshAccessToken($googleAccount);
                    $this->createUpdateGoogleAccount($accessTokeData, $googleAccount);
                    $this->curlSender($url, $postBody, $googleAccount, $method);
                } catch (\Exception $e) {
                    throw new OAuthCompanyException(json_encode($e->getMessage()));
                }
            }
            foreach ($curl->response->error->details as $errorDetails) {
                throw new OAuthCompanyException(json_encode($errorDetails->errorDetails));
            }
        }

        return $curl->response;
    }

    public function createLocation(GoogleAccount $googleAccount, $account, BusinessInfo $businessInfo)
    {
        $url = 'https://mybusiness.googleapis.com/v4/' . $account . '/locations?requestId=' . rand(1, 10000);
        $response = $this->curlSender($url, json_encode($this->getPostBody($businessInfo)), $googleAccount, 'post');
        return $response;
    }

    public function updateLocation(GoogleAccount $googleAccount, $location, BusinessInfo $businessInfo)
    {
        $scope = 'primaryPhone,primaryCategory,additionalCategories,locationName,websiteUrl,profile,regularHours';
        $url = 'https://mybusiness.googleapis.com/v4/' . $location . '?updateMask=' . $scope;
        $postBody = $this->getPostBody($businessInfo);
        $postBody->setName($businessInfo->getName());
        $response = $this->curlSender($url, json_encode($postBody), $googleAccount, 'patch');
        return $response;
    }

    public function createUpdateGoogleAccount($accessTokeData, $googleAccount = null)
    {
        if (array_key_exists('access_token', $accessTokeData)) {
            $createdDate = new \DateTime();
            $createdDate->setTimestamp($accessTokeData['created']);
            if ($googleAccount === null) {
                $googleAccount = new GoogleAccount();
            }

            if (array_key_exists('refresh_token', $accessTokeData)) {
                $googleAccount->setRefreshToken($accessTokeData['refresh_token']);
            }
            if ($googleAccount instanceof GoogleAccount) {
                $googleAccount->setAccessToken($accessTokeData['access_token']);
                $googleAccount->setCreated($createdDate);
                $googleAccount->setExpiresIn(new \DateTime('+ ' . $accessTokeData['expires_in'] . ' seconds'));

                $this->em->persist($googleAccount);
                $this->em->flush();
            }

        }
    }

    public function updateAccountLocation(GoogleAccount $googleAccount, $location)
    {
        $googleAccount->setLocation($location);

        $this->em->persist($googleAccount);

        $this->em->flush();
    }

    /**
     * @param $id
     * @return BusinessInfo
     */
    private function getBusinessByID($id)
    {
        $repository = $this->em->getRepository('SingAppBundle:BusinessInfo');

        /**
         * @var BusinessInfo $business
         */
        $business = $repository->findOneBy(['id' => $id]);

        return $business;
    }
}