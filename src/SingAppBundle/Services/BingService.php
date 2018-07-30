<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\BadResponseException;
use League\OAuth2\Client\Token\AccessToken;
use Microsoft\BingAds\Auth\ApiEnvironment;
use Microsoft\BingAds\Auth\AuthorizationData;
use Microsoft\BingAds\Auth\OAuthDesktopMobileAuthCodeGrant;
use Microsoft\BingAds\Auth\OAuthTokenRequestException;
use Microsoft\BingAds\Auth\OAuthTokens;
use Microsoft\BingAds\Auth\OAuthWebAuthCodeGrant;
use Microsoft\BingAds\Auth\ServiceClient;
use Microsoft\BingAds\Auth\ServiceClientType;
use Microsoft\BingAds\V12\CustomerManagement\GetUserRequest;
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

    private function getCustomerManagementProxy()
    {
        return new ServiceClient(
            ServiceClientType::CustomerManagementVersion12,
            $this->getAuthorizationData(),
            ApiEnvironment::Production
        );
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

    public function createAccount(OAuthTokens $accessTokeData)
    {
        if ($accessTokeData instanceof OAuthTokens) {
            $createdDate = new \DateTime();
            $createdDate->setTimestamp(time());
            $bingAccount = new BingAccount();

            if ($accessTokeData->RefreshToken !== null) {
                $bingAccount->setRefreshToken($accessTokeData->RefreshToken);
            }

            $bingAccount->setAccessToken($accessTokeData->AccessToken);
            $bingAccount->setCreated($createdDate);
            $bingAccount->setExpires($accessTokeData->AccessTokenExpiresInSeconds);

            $this->em->persist($bingAccount);
            $this->em->flush();

        }
    }

    public function getOwner()
    {
        $this->getCustomerManagementProxy()->SetAuthorizationData($this->getProvider());
        $GLOBALS['Proxy'] = $GLOBALS['CustomerManagementProxy'];
        $request = new GetUserRequest();
        $request->UserId = null;
        return $this->getCustomerManagementProxy()->GetService()->GetUser($request)->User;
    }

    public function resetToken(BingAccount $account)
    {
        if($account instanceof BingAccount){
            try
            {
                $refreshToken = $account->getRefreshToken();
                if($refreshToken != null)
                {
                    $token = $this->getAuthorizationData()->Authentication->RequestOAuthTokensByRefreshToken($refreshToken);
                    $account->setRefreshToken($token->RefreshToken);
                    $account->setAccessToken($token->AccessToken);
                    $this->em->persist($account);
                    $this->em->flush();
                }

            }
            catch(OAuthTokenRequestException $e)
            {
                throw new OAuthCompanyException($e->getMessage());
            }
        }
        return $this;
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