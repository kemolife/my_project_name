<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth2\Client\Token\AccessToken;
use Microsoft\BingAds\Auth\AuthorizationData;
use Microsoft\BingAds\Auth\OAuthTokenRequestException;
use Microsoft\BingAds\Auth\OAuthWebAuthCodeGrant;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\BingAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;

class BingService
{
    private $em;
    private $clientId = '4dca26fc-ab69-4b78-b5fa-b0d683005bb0';
    private $developerToken = "118062AN7O421835";
    private $clientSecret = 'lvzpPQ399-wijAZBCZ53{|$';
    private $redirectUrl = "https://listings.devcom.com/bing/oauth2callback";

    private $token;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    private function getProvider()
    {
//        return new Microsoft([
//            // Required
//            'clientId' => $this->clientId,
//            'clientSecret' => $this->clientSecret,
//            'redirectUri' => $this->redirectUrl,
//            // Optional
//            'urlAuthorize'              => 'https://login.windows.net/common/oauth2/authorize',
//            'urlAccessToken'            => 'https://login.windows.net/common/oauth2/token',
//            'urlResourceOwnerDetails' => 'https://outlook.office.com/api/v1.0/me'
//        ]);
        return (new OAuthWebAuthCodeGrant())
            ->withClientId($this->clientId)
            ->withClientSecret($this->clientSecret)
            ->withRedirectUri($this->redirectUrl)
            ->withState(rand(0,999999999));
    }

    private function getAuthorizationData()
    {
        return (new AuthorizationData())
            ->withAuthentication($this->getProvider())
            ->withDeveloperToken($this->developerToken);
    }

    public function auth()
    {
        $provider = $this->getAuthorizationData();
        try {
            return $provider->Authentication->GetAuthorizationEndpoint();
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getToken($url)
    {
        $provider = $this->getAuthorizationData();
        try {
            return $provider->Authentication->RequestOAuthTokensByResponseUri($url);
        } catch (OAuthTokenRequestException $e) {
            throw new OAuthCompanyException($e->getMessage());
        } catch(\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    /**
     * @param BingAccount $bingAccount
     * @return $this
     * @throws OAuthCompanyException
     */
    public function setTokenObject(BingAccount $bingAccount)
    {
        $value = [
            'access_token' => $bingAccount->getAccessToken(),
            'resource_owner_id' => $bingAccount->getResourceOwnerId(),
            'refresh_token' => $bingAccount->getRefreshToken(),
            'expires' => $bingAccount->getExpires()
        ];
        try {
            $this->token = new AccessToken($value);
            return $this;
        }catch (\InvalidArgumentException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function createAccount(AccessToken $accessTokeData)
    {
        if ($accessTokeData instanceof AccessToken) {
            $createdDate = new \DateTime();
            $createdDate->setTimestamp(time());
            $bingAccount = new BingAccount();

            if ($accessTokeData->getRefreshToken() !== null) {
                $bingAccount->setRefreshToken($accessTokeData->getRefreshToken());
            }

            $bingAccount->setAccessToken($accessTokeData->getToken());
            $bingAccount->setCreated($createdDate);
            $bingAccount->setExpires($accessTokeData->getExpires());
            $bingAccount->setResourceOwnerId($accessTokeData->getResourceOwnerId());

            $this->em->persist($bingAccount);
            $this->em->flush();

        }
    }

    public function getOwner()
    {
        $provider = $this->getProvider();
        return $provider->getResourceOwner($this->token);
    }

    /**
     * @return null|BusinessInfo
     */
    protected function getClientData()
    {
        return $this->em->getRepository('SingAppBundle:BusinessInfo')->findOneBy(['user' => $this->user->getId()]);
    }

    /**
     * @return null|BingAccount
     */
    public function getBingAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:BingAccount');
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
        $bing = $this->getProvider();
        $params['name'] = $this->getClientData()->getName();
        $params['address'] = $this->getClientData()->getAddress();
        $params['description'] = $this->getClientData()->getDescription();
        $params['url'] = $this->getClientData()->getWebsite();
//        $params['primaryCategoryId'] = $this->getClientData()->getCategory();
        $params['hours'] = $this->getClientData()->getOpeningHours();

        $response = json_decode($bing->get('venues/' . $venue->id . '/proposeedit', $params, true));
        if ($response->meta->code != 200) {
            throw new OAuthCompanyException('Try later!');
        }

    }
}