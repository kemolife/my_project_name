<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use DirkGroenen\Pinterest\Exceptions\PinterestException;
use DirkGroenen\Pinterest\Pinterest;
use DirkGroenen\Pinterest\Transport\Response;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Entity\PinterestBoard;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Entity\PinterestSection;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class PinterestService
{
    const BASE_URL = 'https://api.pinterest.com';
    const URL_ME = '/v1/me/';
    const URL_PINS = '/v1/me/pins/';

    private $em;
    private $clientId = '4977395529412522088';
    private $clientSecret = 'bd7d90db3f897fc007353d27d283f486b8d71ba98985291177a35cc4fb439b19';
    private $redirectUrl = "https://businesslistings.cubeonline.com.au/pinterest/oauth2callback";
    private $curl;
    private $webDir;
    private $cache;

    public function __construct(EntityManagerInterface $entityManager, $webDir)
    {
        $this->cache = new FilesystemCache();
        $this->em = $entityManager;
        $this->curl = new Curl(self::BASE_URL);
        $this->webDir = $webDir;
    }

    public function auth()
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        return $pinterest->auth->getLoginUrl($this->redirectUrl, array('read_public', 'write_public'));
    }

    public function createAccount(Response $accessTokeData)
    {
        $createdDate = new \DateTime();
        $pinterest = new PinterestAccount();

        $pinterest->setCreated($createdDate);
        $pinterest->setAccessToken($accessTokeData->access_token);

        $this->em->persist($pinterest);
        $this->em->flush();

        $this->savePins($pinterest);
    }

    private function savePins(PinterestAccount $pinterestAccount)
    {
        try {
            foreach ($this->getPins($pinterestAccount->getAccessToken()) as $pin) {
                $pinterestPin = new PinterestPin();
                $pinterestPin->setLink($pin->url);
                $pinterestPin->setTitle($pin->url);
                $pinterestPin->setBoard($pin->board);
                $pinterestPin->setCaption($pin->note);
                $pinterestPin->setStatus('pending');
                $pinterestPin->setPostDate(new \DateTime($pin->created_at));
                $pinterestPin->setSocialNetwork('pinterest');
                $pinterestPin->setBusiness($pinterestAccount->getBusiness());
                $this->em->persist($pinterestPin);
            }
            $this->em->flush();
        }catch (\Exception $e){
            throw new OAuthCompanyException($e->getMessage());
        }
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

    public function getPins($token)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        $pinterest->auth->setOAuthToken($token);
        return $pinterest->users->getMePins()->all();
    }

    public function createPin(PinterestPin $pinterestPin)
    {

        if ($pinterestPin->getAccount() instanceof PinterestAccount) {
            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
            $pinterest->auth->setOAuthToken($pinterestPin->getAccount()->getAccessToken());

            $pinterest->pins->create(array(
                "note" => $pinterestPin->getCaption(),
                "image" => $this->webDir . "/images/" . $pinterestPin->getMedia()[0],
                "link" => $pinterestPin->getLink(),
                "board" => $pinterest->users->me()->toArray()['username'].'/'.$pinterestPin->getBoard()
            ));
        }
    }

//    public function editPin(PinterestPin $pinterestPin)
//    {
//
//        if ($pinterestPin->getAccount() instanceof PinterestAccount) {
//            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
//            $pinterest->auth->setOAuthToken($pinterestPin->getAccount()->getAccessToken());
//
//            $pinterest->pins->edit($pinterestPin->getMedia(), array(
//                "note" => $pinterestPin->getCaption(),
//                "image" => $this->webDir . "/images/" . $pinterestPin->getMedia()[0],
//                "link" => $pinterestPin->getLink(),
//                "board" => $pinterest->users->me()->toArray()['username'].'/'.$pinterestPin->getBoard()
//            ));
//        }
//    }

    public function deletePin($pinId,  PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
            $pinterest->auth->setOAuthToken($pinterestAccount->getAccessToken());
            return $pinterest->pins->delete($pinId);
        }
    }

    public function getBoards(PinterestAccount $pinterestAccount)
    {
        $this->getHash($pinterestAccount, 'pin_boards');
        if ($pinterestAccount instanceof PinterestAccount) {
            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
            $pinterest->auth->setOAuthToken($pinterestAccount->getAccessToken());
            return $pinterest->users->getMeBoards()->toArray();
        }
    }

    public function createBoard($formData, PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
            $pinterest->auth->setOAuthToken($pinterestAccount->getAccessToken());
            $pinterest->boards->create(array(
                "name" => $formData['name'],
                "description" => $formData['description'],
            ));
        }
    }

    public function editBoard($boardId, $formData, PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
            $pinterest->auth->setOAuthToken($pinterestAccount->getAccessToken());
            $pinterest->boards->edit($boardId, array(
                "name" => $formData['name'],
                "description" => $formData['description'],
            ));
        }
    }

    public function deleteBoards($boardId, PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $pinterest = new Pinterest($this->clientId, $this->clientSecret);
            $pinterest->auth->setOAuthToken($pinterestAccount->getAccessToken());
            return $pinterest->boards->delete($boardId);
        }
    }

    public function getPinsByBoard($boardId, PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $this->curl->get('/v1/board/' . $boardId . '/pins/', ['access_token' => $pinterestAccount->getAccessToken()]);
            var_dump($this->curl->response); die;
        }
    }

    public function getSectionsByBoard($boardId, PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $this->curl->get('/v1/board/' . $boardId . '/sections/', ['access_token' => $pinterestAccount->getAccessToken()]);
            var_dump($this->curl->response); die;
            return $this->curl->response;
        }
    }

    public function createSectionByBoard($boardId, $formData, PinterestAccount $pinterestAccount)
    {
        if ($pinterestAccount instanceof PinterestAccount) {
            $this->curl->setUrl('/v1/board/'.$boardId.'/sections/', ['access_token' => $pinterestAccount->getAccessToken()]);

            $this->curl->post($this->curl->url, [
                "title" => $formData['title']
            ]);
        }
    }

    public function deleteSection($sectionId, $token)
    {
        $this->curl->delete('/v1/board/sections/'.$sectionId, ['access_token' => $token]);
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

    private function getHash(PinterestAccount $account, $alias)
    {
        return hash('ripemd160', $alias . 'business'. $account->getBusiness()->getId() . 'user' .  $account->getUser()->getId());
    }
}