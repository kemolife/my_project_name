<?php


namespace SingAppBundle\Services;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FacebookAccount;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use Symfony\Component\HttpFoundation\Request;

class FacebookService
{
    private $domain;

    private $em;

    public function __construct($domain, EntityManagerInterface $entityManager)
    {
        $this->domain = $domain;

        $this->em = $entityManager;
    }

    public function auth()
    {
        $fb = new Facebook([
            'app_id' => '173753973300728',
            'app_secret' => '4ccd427f6d8e6353dbd5531a476d9c65',
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $permissions = [
            'email', 'user_likes', 'user_location',
            'user_posts', 'publish_to_groups', 'user_managed_groups',
            'groups_access_member_info', 'business_management',
            'manage_pages', 'publish_pages', 'pages_manage_cta', 'pages_manage_instant_articles',
            'pages_show_list', ''];

        $loginUrl = $helper->getLoginUrl($this->domain . '/facebook/oauth2callback', $permissions);

        return $loginUrl;
    }

    public function getAccessToken()
    {
        $fb = new Facebook([
            'app_id' => '173753973300728',
            'app_secret' => '4ccd427f6d8e6353dbd5531a476d9c65',
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        return $accessToken;
    }

    public function getAccounts()
    {

        $fb = new Facebook([
            'app_id' => '173753973300728',
            'app_secret' => '4ccd427f6d8e6353dbd5531a476d9c65',
            'default_graph_version' => 'v2.10',
        ]);

        $result = $fb->get('me/accounts');
        return $result;
    }

    public function createFacebookAccount(Request $request, AccessToken $accessTokeData, $businessId)
    {
        $business = $this->getBusinessByID($businessId);

       if ($business instanceof BusinessInfo) {
           $facebookAccount = new FacebookAccount();
           $facebookAccount->setAccessToken($accessTokeData->getValue());
           $facebookAccount->setExpiresIn($accessTokeData->getExpiresAt());
           $facebookAccount->setBusiness($business);

           $this->em->persist($facebookAccount);
           $this->em->flush();
       }

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