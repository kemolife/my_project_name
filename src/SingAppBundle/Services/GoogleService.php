<?php

namespace SingAppBundle\Services;


use Curl\Curl;
use Google_Service_Exception;
use Google_Service_MyBusiness_Account;
use Google_Service_MyBusiness_BusinessHours;
use Google_Service_MyBusiness_Category;
use Google_Service_MyBusiness_LatLng;
use Google_Service_MyBusiness_LocalPost;
use Google_Service_MyBusiness_Location;
use Google_Service_MyBusiness_PostalAddress;
use Google_Service_MyBusiness_Profile;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\GooglePost;
use SingAppBundle\Entity\Media;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Helper\PHPFunctionsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_MyBusiness;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\interfaces\BaseInterface;
use Symfony\Component\HttpFoundation\Request;

class GoogleService implements BaseInterface
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

    public function refreshAccessToken(GoogleAccount &$googleAccount)
    {

        $client = new Google_Client();

        $client->setAuthConfig('client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);
        $client->setRedirectUri($this->domain . '/google/oauth2callback');

        $accessTokenData = $client->fetchAccessTokenWithRefreshToken($googleAccount->getRefreshToken());

        if (array_key_exists('access_token', $accessTokenData) && array_key_exists('refresh_token', $accessTokenData)) {
            $oauthService = new \Google_Service_Oauth2($client);

            $repository = $this->em->getRepository('SingAppBundle:GoogleAccount');

            $googleAccounts = $repository->findBy(['googleId' => $oauthService->userinfo->get()->getId()]);

            /**
             * @var GoogleAccount $account
             */
            foreach ($googleAccounts as $account) {
                $account->setAccessToken($accessTokenData['access_token']);
                $account->setRefreshToken($accessTokenData['refresh_token']);
                $account->setExpiresIn(new \DateTime('+ ' . $accessTokenData['expires_in'] . ' seconds'));

                $this->em->persist($account);
            }

            $googleAccount->setAccessToken($accessTokenData['access_token']);
            $googleAccount->setRefreshToken($accessTokenData['refresh_token']);
            $googleAccount->setExpiresIn(new \DateTime('+ ' . $accessTokenData['expires_in'] . ' seconds'));

            $this->em->persist($googleAccount);

            $this->em->flush();
        }

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
        } catch (Google_Service_Exception $e) {
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
//        $address->setRegionCode($businessInfo->getRegionCode());
//        $address->setAddressLines($businessInfo->getAddress());
//        $address->setAdministrativeArea($businessInfo->getAdministrativeArea());
//        $address->setLocality($businessInfo->getLocality());
//        $address->setPostalCode($businessInfo->getPostalCode());
        $address->setRegionCode('AU');
        $address->setAddressLines('San Telmo, 14 Meyers Pl, Melbourne Victoria, Australia');
        $address->setAdministrativeArea('VIC');
        $address->setLocality('Melbourne');
        $address->setPostalCode(3000);
        $address->setLanguageCode('en');
        $postBody->setAddress($address);

        $latLng = new Google_Service_MyBusiness_LatLng();
//        $latLng->setLatitude($businessInfo->getLatitude());
//        $latLng->setLongitude($businessInfo->getLongitude());
        $latLng->setLatitude(-37.8121636);
        $latLng->setLongitude(144.9724132);
        $postBody->setLatlng($latLng);

        $primaryCategory = new Google_Service_MyBusiness_Category();
        $primaryCategory->setDisplayName($businessInfo->getCategory()->getName());
        $primaryCategory->setCategoryId($businessInfo->getCategory()->getCategoryId());
        $postBody->setPrimaryCategory($primaryCategory);
        $additionalCategory = [];
        foreach ($businessInfo->getAdditionalCategories() as $item) {
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
            {
                $client = new Google_Client();
                $client->setAuthConfig('client_secret.json');
                $client->setAccessToken($accessTokeData['access_token']);
                $client->setAccessType('offline');
                $client->setIncludeGrantedScopes(true);   // incremental auth
                $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

                $oauthService = new \Google_Service_Oauth2($client);

                $createdDate = new \DateTime();
                $createdDate->setTimestamp($accessTokeData['created']);
                if ($googleAccount === null) {
                    $googleAccount = new GoogleAccount();
                }

                if (array_key_exists('refresh_token', $accessTokeData)) {
                    $googleAccount->setRefreshToken($accessTokeData['refresh_token']);
                } else {
                    $repository = $this->em->getRepository('SingAppBundle:GoogleAccount');

                    $googleAccounts = $repository->findBy(['googleId' => $oauthService->userinfo->get()->getId()]);

                    /**
                     * @var GoogleAccount $account
                     */
                    foreach ($googleAccounts as $account) {
                        if ($account->getRefreshToken()) {
                            $googleAccount->setRefreshToken($account->getRefreshToken());

                            break;
                        }
                    }
                }

                if ($googleAccount instanceof GoogleAccount) {
                    $googleAccount->setAccessToken($accessTokeData['access_token']);
                    $googleAccount->setCreated($createdDate);
                    $googleAccount->setExpiresIn(new \DateTime('+ ' . $accessTokeData['expires_in'] . ' seconds'));
                    $googleAccount->setGoogleId($oauthService->userinfo->get()->getId());

                    $this->em->persist($googleAccount);
                    $this->em->flush();
                }
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

    public function createPost(GooglePost $post)
    {
        if ($post->getAccount() instanceof GoogleAccount) {
            $client = new Google_Client();
            $client->setAuthConfig('client_secret.json');
            $client->setAccessToken($post->getAccount()->getAccessToken());
            $client->setAccessType('offline');
            $client->setIncludeGrantedScopes(true);   // incremental auth
            $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

            $googleMyBusiness = new Google_Service_MyBusiness($client);
            $googlePost = new Google_Service_MyBusiness_LocalPost();
            $googlePost->setName($post->getTitle());
            $googlePost->setSummary($post->getCaption());
            $googlePost->setLanguageCode('en');

            $googleMediaArray = [];

            /**
             * @var Media $media
             */
            foreach ($post->getMedia() as $media) {
                $mimeType = mime_content_type($media->getPath());

                $googleMedia = new \Google_Service_MyBusiness_MediaItem();

                if (strpos($mimeType, 'image') !== false) {
                    $type = 'PHOTO';
                } else {
                    $type = 'VIDEO';
                }

                $googleMedia->setMediaFormat($type);
                $googleMedia->setSourceUrl($this->domain . '/' . $media->getPath());

                $googleMediaArray[] = $googleMedia;
            }

            $googlePost->setMedia($googleMediaArray);


            try {
                $googleMyBusiness->accounts_locations_localPosts->create($post->getAccount()->getLocation(), $googlePost);
            } catch (\Exception $exception) {
                $error = json_decode($exception->getMessage(), true);

                if ($error['error']['status'] == 'UNAUTHENTICATED') {
                    $googleAccount = $post->getAccount();
                    $this->refreshAccessToken($googleAccount);
                    $this->createPost($post);
                } else {
                    throw new \Google_Exception($exception->getMessage());
                }
            }
        } else {
            throw new \Google_Exception('You are not connected a Google My Business account');
        }
    }

    public function getReviews(GoogleAccount $googleAccount)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessToken($googleAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

        $googleMyBusiness = new Google_Service_MyBusiness($client);

        try {
            $reviews = $googleMyBusiness->accounts_locations_reviews->listAccountsLocationsReviews($googleAccount->getLocation())->getReviews();
        } catch (\Exception $exception) {
            $error = json_decode($exception->getMessage(), true);

            if ($error['error']['status'] == 'UNAUTHENTICATED') {
                $this->refreshAccessToken($googleAccount);
                $reviews = $this->getReviews($googleAccount);
            } else {
                throw new \Google_Exception($exception->getMessage());
            }
        }


        return $reviews;
    }

    public function reply(GoogleAccount $googleAccount, $reviewId, $comment)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessToken($googleAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

        $googleMyBusiness = new Google_Service_MyBusiness($client);

        $reply = new \Google_Service_MyBusiness_ReviewReply();
        $reply->setComment($comment);

        try {
            $googleMyBusiness->accounts_locations_reviews->updateReply($reviewId, $reply);
        } catch (\Exception $exception) {
            $error = json_decode($exception->getMessage(), true);

            if ($error['error']['status'] == 'UNAUTHENTICATED') {
                $this->refreshAccessToken($googleAccount);
                $this->reply($googleAccount, $reviewId, $comment);
            } else {
                throw new \Google_Exception($exception->getMessage());
            }
        }

    }

    public function editAccount(SocialNetworkAccount $googleAccount, BusinessInfo $business)
    {
        $scope = 'primaryPhone,primaryCategory,additionalCategories,locationName,websiteUrl,profile,regularHours';
        $url = 'https://mybusiness.googleapis.com/v4/' . $googleAccount->getLocation() . '?updateMask=' . $scope;
        $postBody = $this->getPostBody($business);
        $postBody->setName($business->getName());
        $response = $this->curlSender($url, json_encode($postBody), $googleAccount, 'patch');
        return $response;
    }

    public function removePost(GooglePost $post)
    {
        $googleAccount = $post->getAccount();

        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessToken($googleAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

        $googleMyBusiness = new Google_Service_MyBusiness($client);

        try {
            $googleMyBusiness->accounts_locations_localPosts->delete($post->getGooglePostName());
        } catch (\Exception $exception) {
            $error = json_decode($exception->getMessage(), true);

            if ($error['error']['status'] == 'UNAUTHENTICATED') {
                $this->refreshAccessToken($googleAccount);
                $this->removePost($post);
            } else {
                throw new \Google_Exception($exception->getMessage());
            }
        }
    }

    public function searchBusiness(BusinessInfo $business, $account = null)
    {
        $searchObject = new \StdClass();
        $searchObject->status = self::STATUS_FALSE;
        $searchObject->name = null;
        $searchObject->address = null;
        $searchObject->phone = null;
        if($account instanceof GoogleAccount && null !== $account->getGoogleId()){
            $client = new Google_Client();
            $client->setAuthConfig('client_secret.json');
            $client->setAccessToken($account->getAccessToken());
            $client->setAccessType('offline');
            $client->setIncludeGrantedScopes(true);   // incremental auth
            $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

            $googleMyBusiness = new Google_Service_MyBusiness($client);

            try {
                $location = $googleMyBusiness->accounts_locations->get($account->getGoogleId());
                $searchObject->status = self::STATUS_TRUE;
                $searchObject->name = $location->getName();
                $searchObject->address = $location->getLocationName();
                $searchObject->phone = $location->getPrimaryPhone();
            } catch (\Exception $exception) {
                $error = json_decode($exception->getMessage(), true);

                if ($error['error']['status'] == 'UNAUTHENTICATED') {
                    $this->refreshAccessToken($googleAccount);
                    $this->searchBusiness($account, $business);
                }
            }
        }

        return $searchObject;
    }
}