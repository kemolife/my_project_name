<?php


namespace SingAppBundle\Services;


use Facebook\FacebookResponse;
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
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\HttpFoundation\Request;

class FacebookService
{
    private $domain;

    private $em;

    private static $workHours = [
        'Monday' => ['mon_1_open', 'mon_1_close'],
        'Tuesday' => ['tue_1_open', 'tue_1_close'],
        'Wednesday'=> ['wed_1_open', 'wed_1_close'],
        'Thursday'=> ['thu_1_open', 'thu_1_close'],
        'Friday'=> ['fri_1_open', 'fri_1_close'],
        'Saturday'=> ['sat_1_open', 'sat_1_close'],
        'Sunday'=> ['sun_1_open', 'sun_1_close']
    ];

    public function __construct($domain, EntityManagerInterface $entityManager)
    {
        $this->domain = $domain;

        $this->em = $entityManager;
    }

    private function clientSettings($version)
    {
        return new Facebook([
            'app_id' => '214454595877928',
            'app_secret' => '1c94e55cee9db82948c697720823fe9d',
            'default_graph_version' => $version,
        ]);
    }

    public function auth()
    {
        $fb = $this->clientSettings('v2.10');

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
        $fb = $this->clientSettings('v2.10');

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            throw new OAuthCompanyException($e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }

        return $accessToken;
    }

    public function getPages(FacebookAccount $facebookAccount)
    {
        $fb = $this->clientSettings('v3.1');

        $result = $fb->get('me/accounts', $facebookAccount->getAccessToken());

        return @$result->getDecodedBody()['data'];
    }

    public function editPage(FacebookAccount $facebookAccount)
    {
        $fb = $this->clientSettings('v3.1');
        $obj = new \StdClass();
        $obj->city = 'Hackett';
        $obj->state = 'AL';
        $obj->zip = 36310;
        $obj->country = 'USA';
        $day =  $categoryList = [];
        foreach (\GuzzleHttp\json_decode($facebookAccount->getBusiness()->getOpeningHours())->days as $key => $item) {
            if ($item->type === 'open') {
                $day[self::$workHours[$key][0]] = $item->slots[0]->start;
                $day[self::$workHours[$key][1]] = $item->slots[1]->end;
            }
        }
        foreach ($facebookAccount->getBusiness()->getAdditionalCategories() as $category){
            array_push($categoryList, $category->getName());
        }
        try {
            $fb->post(
                '/' . $facebookAccount->getPage(),
                array(
                    'name' => $facebookAccount->getBusiness()->getName(),
//                    'category' => $facebookAccount->getBusiness()->getCategory()->getName(),
//                    'category_list' => $categoryList,
                    'about' => $facebookAccount->getBusiness()->getDescription(),
                    'phone' => $facebookAccount->getBusiness()->getPhoneNumber(),
                    'website' => $facebookAccount->getBusiness()->getWebsite(),
                    'hours' => json_encode($day),
                    'location' => $obj,
                    'emails' => [$facebookAccount->getBusiness()->getEmail()]
                ),
                $facebookAccount->getPageAccessToken()
            );
        } catch (FacebookResponseException $e) {
            throw new OAuthCompanyException($e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function createPost(FacebookPost $facebookPost)
    {
        $result = null;
        $fb = $this->clientSettings('v3.1');
        try {
            foreach ($facebookPost->getMedia() as $media) {
                $mimeType = mime_content_type($media->getPath());


                if (strpos($mimeType, 'image') !== false) {
                    $result = $fb->post(
                        '/' . $facebookPost->getAccount()->getPage() . '/photos',
                        array(
                            'caption' => $facebookPost->getCaption(),
                            'url' => $this->domain . "/" . $media->getPath(),
                        ),
                        $facebookPost->getAccount()->getPageAccessToken()
                    );
                } else {
                    $result = $fb->post(
                        '/' . $facebookPost->getAccount()->getPage() . '/videos',
                        array(
                            'description' => $facebookPost->getCaption(),
                            'source' => $this->domain . "/" . $media->getPath(),
                        ),
                        $facebookPost->getAccount()->getPageAccessToken()
                    );
                }
            }
            if($result instanceof FacebookResponse && $result->getHttpStatusCode() === 200) {
                $facebookPost->setPostId(@$result->getDecodedBody()['data']['post_id']);
                $this->em->persist($facebookPost);
                $this->em->flush();
            }
        } catch (FacebookResponseException $e) {
            throw new OAuthCompanyException($e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getAllPost(FacebookAccount $facebookAccount)
    {
        try {
            $fb = $this->clientSettings('v3.1');
            $response = $fb->get(
                '/' . $facebookAccount->getPage() . '/posts',
                $facebookAccount->getPageAccessToken()
            );
            return @$response->getDecodedBody()['data'];
        } catch (FacebookResponseException $e) {
            throw new OAuthCompanyException($e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function setPosts(FacebookAccount $facebookAccount)
    {
        $repository = $this->em->getRepository('SingAppBundle:FacebookPost');

        $posts = $this->getAllPost($facebookAccount);
        foreach ($posts as $post) {
            if($repository->findOneBy(['postId' => $post['id']]) === null) {
                $facebookPost = new FacebookPost();
                $facebookPost->setTitle(substr($post['message'], 0, 100));
                $facebookPost->setCaption($post['message']);
                $facebookPost->setPostDate(new \DateTime($post['created_time']));
                $facebookPost->setSocialNetwork('facebook');
                $facebookPost->setBusiness($facebookAccount->getBusiness());
                $facebookPost->setPostId($post['id']);
                $facebookPost->setStatus('posted');
                $this->em->persist($facebookPost);
            }
        }
    }

    public function getAccounts()
    {

        $fb = $this->clientSettings('v2.10');

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

    public function removePost(FacebookPost $facebookPost)
    {
        $facebookAccount = $facebookPost->getAccount();

        try {
            $fb = $this->clientSettings('v3.1');
            $fb->delete(
                '/' . $facebookPost->getPostId(),
                array(),
                $facebookAccount->getPageAccessToken()
            );
        } catch (FacebookResponseException $e) {
            throw new OAuthCompanyException($e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }
}