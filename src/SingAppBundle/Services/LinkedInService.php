<?php

namespace SingAppBundle\Services;


use Curl\Curl;
use LinkedIn\LinkedIn;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\LinkedinAccount;
use SingAppBundle\Entity\LinkedinPost;
use SingAppBundle\Providers\Exception\OAuthCompanyException;

class LinkedInService
{
    const BASE_URL = 'https://api.pinterest.com';
    const URL_ME = '/v1/me/';
    const URL_PINS = '/v1/me/pins/';
    const SCOPE_SHARED = 'w_share';
    const SCOPE_COMPANY = 'rw_company_admin';

    private $em;
    private $clientId = '77i9fa6oxrurcs';
    private $clientSecret = '9tFllQUeppzpotPO';
    private $redirectUrl = "/linkedin/oauth2callback";
    private $curl;
    private $webDir;
    private $domain;

    public function __construct(EntityManagerInterface $entityManager, $webDir, $domain)
    {
        $this->em = $entityManager;
        $this->curl = new Curl(self::BASE_URL);
        $this->webDir = $webDir;
        $this->domain = $domain;
    }

    private function settingsClient()
    {
        return new LinkedIn(
            [
                'api_key' => $this->clientId,
                'api_secret' => $this->clientSecret,
                'callback_url' => $this->domain . $this->redirectUrl
            ]
        );
    }

    private function settingsClientWithToken($token)
    {
        $li = $this->settingsClient();
        $li->setAccessToken($token);
        return $li;
    }

    public function auth()
    {

        return $url = $this->settingsClient()->getLoginUrl(
            array(
                LinkedIn::SCOPE_BASIC_PROFILE,
                LinkedIn::SCOPE_EMAIL_ADDRESS,
                self::SCOPE_SHARED,
                self::SCOPE_COMPANY
            )
        );
    }

    public function createAccount(array $accessTokeData)
    {
        $createdDate = new \DateTime();
        $linkedin = new LinkedinAccount();

        $linkedin->setCreated($createdDate);
        $linkedin->setAccessToken($accessTokeData['token']);
        $linkedin->setExpiration($accessTokeData['expiration']);

        $this->em->persist($linkedin);
        $this->em->flush();
    }


    public function getToken($code)
    {
        $li = $this->settingsClient();
        $token = $li->getAccessToken($code);
        $token_expires = $li->getAccessTokenExpiration();

        return ['token' => $token, 'expiration' => $token_expires];
    }

    public function uploadPost(LinkedinPost $linkedinPost)
    {
        $account = $linkedinPost->getAccount();
        $li = $this->settingsClientWithToken($account->getAccessToken());
        $media = $linkedinPost->getMedia()[0];

        $body = [
            'content' => [
                'title' => $linkedinPost->getTitle(),
                'description' => $linkedinPost->getCaption(),
                'submitted-url' => $linkedinPost->getUrl(),
                'submitted-image-url' => $media === null?'':$this->domain . '/' . $media->getPath()
            ],
            "visibility" => [
                "code" => $linkedinPost->getVisibility()
            ]
        ];
        try {
            $response = $li->post('people/~/shares', $body);
        }catch (\RuntimeException $e){
            throw new OAuthCompanyException(json_encode($e->getMessage()));
        }
        if(array_key_exists('updateKey', $response)){
            $linkedinPost->setPostId($response['updateUrl']);
            $linkedinPost->setStatus('posted');
            $this->em->persist($linkedinPost);
            $this->em->flush();
        }else{
            throw new OAuthCompanyException(json_encode($response));
        }
    }
}