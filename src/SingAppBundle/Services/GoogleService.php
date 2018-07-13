<?php

namespace SingAppBundle\Services;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Helper\PHPFunctionsHelper;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_MyBusiness;
use Symfony\Component\HttpFoundation\Request;

class GoogleService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth()
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/google/oauth2callback');


        return $client->createAuthUrl();
    }

    public function getAccessToken($code)
    {

        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/google/oauth2callback');

        return  $client->authenticate($code);
    }

    public function getLocations(GoogleAccount $googleAccount)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setAccessToken($googleAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes(['https://www.googleapis.com/auth/plus.business.manage']);

        $googleMyBusiness = new Google_Service_MyBusiness($client);
        return $googleMyBusiness->categories->listCategories();
    }

    public function createGoogleAccount(Request $request, $accessTokeData)
    {
        $neededDataKeys =['access_token', 'token_type', 'expires_in', 'refresh_token', 'created'];

        $business = $this->getBusinessByUID($request->cookies->get('business'));

        if (PHPFunctionsHelper::array_keys_exist($neededDataKeys, $accessTokeData) && $business instanceof BusinessInfo) {
            $createdDate = new \DateTime();
            $createdDate->setTimestamp($accessTokeData['created']);

            $googleAccount = new GoogleAccount();
            $googleAccount->setAccessToken($accessTokeData['access_token']);
            $googleAccount->setRefreshToken($accessTokeData['refresh_token']);
            $googleAccount->setCreated($createdDate);
            $googleAccount->setExpiresIn(new \DateTime('+ '.$accessTokeData['expires_in'].' seconds'));
            $googleAccount->setBusiness($business);

            $this->em->persist($googleAccount);
            $this->em->flush();
        }

    }

    /**
     * @param $uid
     * @return BusinessInfo
     */
    private function getBusinessByUID($uid)
    {
        $repository = $this->em->getRepository('SingAppBundle:BusinessInfo');

        /**
         * @var BusinessInfo $business
         */
        $business = $repository->findOneBy(['id' => 1]);

        return $business;
    }
}