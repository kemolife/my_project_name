<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use DirkGroenen\Pinterest\Exceptions\PinterestException;
use DirkGroenen\Pinterest\Pinterest;
use DirkGroenen\Pinterest\Transport\Response;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\HttpFoundation\Request;

class PinterestService
{
    const BASE_URL = 'https://api.pinterest.com';
    const URL_ME = '/v1/me/';
    const URL_PINS = '/v1/me/pins/';

    private $em;
    private $clientId = '4977395529412522088';
    private $clientSecret = 'bd7d90db3f897fc007353d27d283f486b8d71ba98985291177a35cc4fb439b19';
    private $redirectUrl = "https://listings.devcom.com/pinterest/oauth2callback";
    private $curl;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->curl = new Curl(self::BASE_URL);
        $this->curl->setDefaultJsonDecoder(function ($item){
            return json_decode($item, true);
        });
    }

    public function auth()
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        return $pinterest->auth->getLoginUrl($this->redirectUrl, array('read_public'));
    }

    public function createAccount(Response $accessTokeData)
    {
        $createdDate = new \DateTime();
        $pinterest = new PinterestAccount();

        $pinterest->setCreated($createdDate);
        $pinterest->setAccessToken($accessTokeData->access_token);

        $this->em->persist($pinterest);
        $this->em->flush();
    }


    public function getToken($code)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        try {
            return $pinterest->auth->getOAuthToken($code);
        } catch (PinterestException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getMe($token)
    {
        $this->curl->get(self::URL_ME, ['access_token' => $token]);
        var_dump($this->curl->response);
    }

    public function getMeSdk($token)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        $pinterest->auth->setOAuthToken($token);
        var_dump($pinterest->users->me()->toArray());
    }

    public function getBoardsSdk($token)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        $pinterest->auth->setOAuthToken($token);
        var_dump($pinterest->users->getMeBoards()->all());
    }

    public function createPin($token)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        $pinterest->auth->setOAuthToken($token);
        $pinterest->pins->create(array(
            "note"          => "Test board from API",
            "image"         => "/images/stars.png",
            "board"         => "dirkgroenen/pinterest-api-test"
        ));
    }

    public function getPins($token)
    {
        $this->curl->get(self::URL_PINS, ['access_token' => $token]);
        var_dump($this->curl->response);
    }

    /**
     * @return null|BusinessInfo
     */
    protected function getClientData()
    {
        return $this->em->getRepository('SingAppBundle:BusinessInfo')->findOneBy(['user' => $this->user->getId()]);
    }

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @return null|object
     */
    public function getPinterestAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:PinterestAccount');
        return $repository->findOneBy(['user' => $user, 'business' => $business]);
    }

    /**
     * @throws OAuthCompanyException
     */
    public function updateAccounts($venues)
    {
        foreach ($venues->response->venues->items as $venue) {
            $this->updateAccount($venue);
        }
    }

    private function updateAccount($venue)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        $params['name'] = $this->getClientData()->getName();
        $params['address'] = $this->getClientData()->getAddress();
        $params['description'] = $this->getClientData()->getDescription();
        $params['url'] = $this->getClientData()->getWebsite();
//        $params['primaryCategoryId'] = $this->getClientData()->getCategory();
        $params['hours'] = $this->getClientData()->getOpeningHours();

        $response = json_decode($pinterest->GetPrivate('venues/' . $venue->id . '/proposeedit', $params, true));
        if ($response->meta->code != 200) {
            throw new OAuthCompanyException('Try later!');
        }

    }
}