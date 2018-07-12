<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use FoursquareApi;
use GuzzleHttp\Exception\BadResponseException;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FoursquareAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;

class BingService
{
    private $em;
    private $clientId = '3KB2UCRUVZKBWAKMFLXEKO510XE52JFJTKQNW520AG3FZ514';
    private $clientSecret = 'S2ICWBIL4HZG1B4DPWH4ABWTENGJXTKZ2NDQ5FZLVZ3CIP1G';
    private $redirectUrl = "http://localhost:8001/bing-oauth2callback";

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth()
    {
        $provider = $this->getProvider();
        try {
            return $provider->getAuthorizationUrl();
        }catch (BadResponseException $e){
            throw new OAuthCompanyException($e->getMessage());
        }
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
        $provider = $this->getProvider();
        try {
            return $provider->getAccessToken('authorization_code', ['code' => $code]);
        }catch (\Exception $e){
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    private function getProvider()
    {
        return new Microsoft([
            // Required
            'clientId'                  =>  $this->clientId,
            'clientSecret'              =>  $this->clientSecret,
            'redirectUri'               =>  $this->redirectUrl,
            // Optional
            'urlAuthorize'              => 'https://login.live.com/oauth20_authorize.srf',
            'urlAccessToken'            => 'https://login.live.com/oauth20_token.srf',
            'urlResourceOwnerDetails'   => 'https://outlook.office.com/api/v1.0/me'
        ]);
    }

    public function getOwner($token)
    {
        $provider = $this->getProvider();
        var_dump($provider->getResourceOwner($token)); die;
    }

    /**
     * @return null|BusinessInfo
     */
    protected function getClientData()
    {
        return $this->em->getRepository('SingAppBundle:BusinessInfo')->findOneBy(['user' => $this->user->getId()]);
    }

    /**
     * @return null|FoursquareAccount
     */
    public function getFoursquareSetting(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:FoursquareAccount');
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