<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use FoursquareApi;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FoursquareAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\HttpFoundation\Request;

class FoursquareService
{
    private $em;
    private $clientId = '3KB2UCRUVZKBWAKMFLXEKO510XE52JFJTKQNW520AG3FZ514';
    private $clientSecret = 'S2ICWBIL4HZG1B4DPWH4ABWTENGJXTKZ2NDQ5FZLVZ3CIP1G';
    private $redirectUrl = "https://businesslistings.cubeonline.com.au/foursquare/oauth2callback";

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth()
    {
        $foursquare = new FoursquareAPI($this->clientId, $this->clientSecret);
        return $foursquare->AuthenticationLink( $this->redirectUrl);
    }

    public function getToken($code)
    {
        $foursquare = new FoursquareAPI($this->clientId, $this->clientSecret);
        return $foursquare->GetToken($code, $this->redirectUrl);
    }

    public function createAccount($accessToken)
    {
        $createdDate = new \DateTime();
        $Foursquare = new FoursquareAccount();

        $Foursquare->setCreated($createdDate);
        $Foursquare->setAccessToken($accessToken);

        $this->em->persist($Foursquare);
        $this->em->flush();

        return $Foursquare;
    }

    public function updateFoursquareAccount(FoursquareAccount $foursquareAccount, $accessToken)
    {
        $foursquareAccount->setAccessToken($accessToken);
        $this->em->persist($foursquareAccount);
        $this->em->flush();

        return $foursquareAccount;
    }

    public function getAndUpdatePrivateVenues($token)
    {
        $foursquare = new FoursquareAPI($this->clientId, $this->clientSecret);
        $foursquare->SetAccessToken($token);
        $venues = json_decode($foursquare->GetPrivate('venues/managed'));
        $this->updateAccounts($venues);
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