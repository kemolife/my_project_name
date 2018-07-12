<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use DirkGroenen\Pinterest\Exceptions\PinterestException;
use DirkGroenen\Pinterest\Pinterest;
use Doctrine\ORM\EntityManagerInterface;
use FoursquareApi;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FoursquareAccount;
use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\HttpFoundation\Request;

class PinterestService
{
    private $em;
    private $clientId = '4977395529412522088';
    private $clientSecret = 'bd7d90db3f897fc007353d27d283f486b8d71ba98985291177a35cc4fb439b19';
    private $redirectUrl = "https://localhost:8001/pinterest-oauth2callback";

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth()
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        return $pinterest->auth->getLoginUrl($this->redirectUrl, array('read_public'));
    }

    public function createAccount(BusinessInfo $business, $code)
    {
        $createdDate = new \DateTime();
        $Foursquare = new FoursquareAccount();

        $Foursquare->setCreated($createdDate);
        $Foursquare->setBusiness($business);
        $Foursquare->setCode($code);

        $this->em->persist($Foursquare);
        $this->em->flush();

        return $Foursquare;
    }


    public function getToken($code)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        try{
            return $pinterest->auth->getOAuthToken($code);
        }catch (PinterestException $e){
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getAndUpdatePrivateVenues($token)
    {
        $pinterest = new Pinterest($this->clientId, $this->clientSecret);
        $pinterest->auth->setOAuthToken($token->access_token);
        $me = $pinterest->users->me();
        var_dump($me); die;
    }

    /**
     * @return null|BusinessInfo
     */
    protected function getClientData()
    {
        return $this->em->getRepository('SingAppBundle:BusinessInfo')->findOneBy(['user' => $this->user->getId()]);
    }

    /**
     * @return null|PinterestAccount
     */
    public function getPinterestSetting(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:PinterestAccount');
        $foursquare = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $foursquare;
    }

    /**
     * @throws OAuthCompanyException
     */
    public function updateAccounts($venues)
    {
        foreach ($venues->response->venues->items as $venue){
            $this->updateAccount($venue);
        }
    }

    private function updateAccount($venue)
    {
        $foursquare = new FoursquareAPI($this->clientId, $this->clientSecret);
        $params['name'] = $this->getClientData()->getName();
        $params['address'] = $this->getClientData()->getAddress();
        $params['description'] = $this->getClientData()->getDescription();
        $params['url'] = $this->getClientData()->getWebsite();
//        $params['primaryCategoryId'] = $this->getClientData()->getCategory();
        $params['hours'] = $this->getClientData()->getOpeningHours();

        $response = json_decode($foursquare->GetPrivate('venues/' . $venue->id . '/proposeedit', $params, true));
        if($response->meta->code != 200 ){
            throw new OAuthCompanyException('Try later!');
        }

    }
}