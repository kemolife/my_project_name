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
use SingAppBundle\Entity\FacebookPost;
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
            'app_id' => '214454595877928',
            'app_secret' => '1c94e55cee9db82948c697720823fe9d',
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $permissions = [
            'email', 'user_likes', 'user_location',
            'user_posts', 'publish_to_groups',
            'groups_access_member_info', 'business_management',
            'manage_pages', 'publish_pages', 'pages_manage_cta', 'pages_manage_instant_articles',
            'pages_show_list', 'manage_pages'];

        $loginUrl = $helper->getLoginUrl($this->domain . '/facebook/oauth2callback', $permissions);

        return $loginUrl;
    }

    public function getAccessToken()
    {
        $fb = new Facebook([
            'app_id' => '214454595877928',
            'app_secret' => '1c94e55cee9db82948c697720823fe9d',
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

    public function getPages(FacebookAccount $facebookAccount)
    {
        $fb = new Facebook([
            'app_id' => '214454595877928',
            'app_secret' => '1c94e55cee9db82948c697720823fe9d',
            'default_graph_version' => 'v3.1',
        ]);

        $result = $fb->get('me/accounts', $facebookAccount->getAccessToken());

        return @$result->getDecodedBody()['data'];
    }

    public function createPost(FacebookPost $facebookPost)
    {
        $fb = new Facebook([
            'app_id' => '214454595877928',
            'app_secret' => '1c94e55cee9db82948c697720823fe9d',
            'default_graph_version' => 'v3.1',
        ]);

        $result = $fb->post(
            '/'.$facebookPost->getAccount()->getPage().'/feed',
            array (
                'message' => $facebookPost->getCaption(),
                'link' => $facebookPost->getLink(),
                'picture' => $this->domain . "/" . $facebookPost->getMedia()[0]->getPath(),
                'published' => true
            ),
            $facebookPost->getAccount()->getPageAccessToken()
        );

        var_dump($result); die;
    }

    public function getAccounts()
    {

        $fb = new Facebook([
            'app_id' => '214454595877928',
            'app_secret' => '1c94e55cee9db82948c697720823fe9d',
            'default_graph_version' => 'v2.10',
        ]);

        $result = $fb->get('me/accounts');
        return $result;
    }

    public function createFacebookAccount(Request $request, AccessToken $accessTokeData)
    {
        $facebookAccount = new FacebookAccount();
        $facebookAccount->setAccessToken($accessTokeData->getValue());
        $facebookAccount->setExpiresIn($accessTokeData->getExpiresAt());

        $this->em->persist($facebookAccount);
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