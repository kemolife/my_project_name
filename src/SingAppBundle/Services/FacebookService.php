<?php


namespace SingAppBundle\Service;


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
            'app_id' => '1862006383843024',
            'app_secret' => 'aa5188ade069106fab8e13b304b4ecb4',
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
            'app_id' => '1862006383843024',
            'app_secret' => 'aa5188ade069106fab8e13b304b4ecb4',
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
            'app_id' => '1862006383843024',
            'app_secret' => 'aa5188ade069106fab8e13b304b4ecb4',
            'default_graph_version' => 'v2.10',
        ]);

        $result = $fb->get('me/accounts');

    }

    public function createFacebookAccount(Request $request, AccessToken $accessTokeData)
    {
        $business = $this->getBusinessByUID($request->cookies->get('business'));

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
     * @param $uid
     * @return Business
     */
    private function getBusinessByUID($uid)
    {
        $repository = $this->em->getRepository('AppBundle:Business');

        /**
         * @var Business $business
         */
        $business = $repository->findOneBy(['uid' => $uid]);

        return $business;
    }
}