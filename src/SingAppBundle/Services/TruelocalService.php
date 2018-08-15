<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\truelocalAccount;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\interfaces\BaseInterface;
use SingAppBundle\Services\interfaces\CreateServiceAccountInterface;
use SingAppBundle\Services\interfaces\ScraperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class TruelocalService implements BaseInterface, ScraperInterface, CreateServiceAccountInterface
{
    private $em;
    private $curl;
    private $webDir;
    /**
     * @var BusinessInfo
     */
    private $business;
    private $message = 'truelocal service error, please connect to technical advice';
    private $url = 'https://api.truelocal.com.au/rest';
    private $urlLogin = 'https://api.truelocal.com.au/rest/auth/login?passToken=V0MxbDBlV2VNUw==';
    private $createAccount = 'https://api.truelocal.com.au/rest/auth/signupweb?passToken=V0MxbDBlV2VNUw==';
    private $session;

    private static $stateAbr = [
        'New South Wales' => 'NSW',
        'Queensland' => 'QLD',
        'South Australia' => 'SA',
        'Tasmania' => 'TAS',
        'Victoria' => 'VIC',
        'Western Australia' => 'WA',
        'Australian Capital Territory' => 'ACT',
        'Northern Territory' => 'NT',
    ];

    /**
     * truelocalService constructor.
     * @param EntityManagerInterface $entityManager
     * @param $webDir
     * @param Session $session
     */
    public function __construct(EntityManagerInterface $entityManager, $webDir, Session $session)
    {
        $this->em = $entityManager;
        $this->webDir = $webDir;
        $this->curl = $curl = new Curl();
        $this->session = $session;
    }

    /**
     * @param SocialNetworkAccount $truelocalAccount
     * @return mixed|null
     * @throws OAuthCompanyException
     */
    public function auth(SocialNetworkAccount $truelocalAccount)
    {
        $companyId = null;

        if ($truelocalAccount instanceof TruelocalAccount) {
            $this->curl->setHeaders(
                [
                    'content-type' =>  'application/json'
                ]
            );
            $params['email'] = $truelocalAccount->getUserEmail();
            $params['password'] = $truelocalAccount->getUserPassword();
            $this->curl->post($this->urlLogin, json_encode($params));
            if(isset($this->curl->response->meta) && $this->curl->response->meta->httpCode === 200){
                $token = $this->getToken($this->curl->response->data);
                $myProfileUrl = $this->getMyProfile($this->curl->response->data);
            }else{
                throw new OAuthCompanyException('Login or password incorrect');
            }
            $this->saveCookies('truelocal_' . $truelocalAccount->getUserEmail(),    ['token'=>$token]);
        }
        return $this->url.$myProfileUrl.'?passToken='.$token;
    }

    public function getProfileData($url, SocialNetworkAccount $truelocalAccount)
    {
        $response = null;
        $this->curl->get($url);
        if(isset($this->curl->response->meta) && $this->curl->response->meta->httpCode === 200) {
            $response = $this->curl->response->data->id;
        }else{
           $url = $this->auth($truelocalAccount);
           $this->getProfileData($url, $truelocalAccount);
        }
        return $response;
    }

    /**
     * @param SocialNetworkAccount $truelocalAccount
     * @param $companyId
     * @return SocialNetworkAccount
     */
    public function createAccount(SocialNetworkAccount $truelocalAccount, $profile)
    {
        $createdDate = new \DateTime();

        $truelocalAccount->setCreated($createdDate);
        $truelocalAccount->setProfile($profile);

        $this->em->persist($truelocalAccount);
        $this->em->flush();

        return $truelocalAccount;
    }

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @return null|object
     */

    public function getAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:TruelocalAccount');
        $truelocal = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $truelocal;
    }

    /**
     * @param SocialNetworkAccount $truelocalAccount
     * @param BusinessInfo $business
     * @throws OAuthCompanyException
     */
    public function editAccount(SocialNetworkAccount $truelocalAccount, BusinessInfo $business)
    {
        if(strpos($business->getPhoneNumber(), '+61') === false){
            throw new OAuthCompanyException('You need australian address or phone');
        }
        $this->business = $business;
        $fileName = $this->webDir . '/cookies/cookies_truelocal_' . $truelocalAccount->getUserEmail() . '.txt';
        if (file_exists($fileName) && $truelocalAccount instanceof truelocalAccount) {
            $cookies = json_decode(file_get_contents($fileName), true);
            $this->curl->setHeaders(['content-type' =>  'application/json']);
            $address = new \stdClass();
//            $address->suburb = strtoupper($business->getLocality());
//            $address->postCode = $business->getPostalCode();
//            $address->state = strtoupper(self::$stateAbr[$business->getAdministrativeArea()]);
            $address->suburb = 'Melbourne';
            $address->postCode = 3000;
            $address->state = 'VIC';
            $params['firstName'] = $business->getName();
            $params['displayName'] = $business->getName();
            $params['address'] = $address;
            $params['description'] = $business->getDescription();
            $params['phoneNumber'] = '0'.trim($business->getPhoneNumber(), '+61');
            $params['hideSuburb'] = false;
            $this->curl->post('https://api.truelocal.com.au/rest/users/'.$truelocalAccount->getProfile().'/update?passToken=' . $cookies['token'], json_encode($params));
            if ($this->curl->response->meta->httpCode === 400) {
                throw new OAuthCompanyException(json_encode($this->curl->response->meta->errors));
            }
            if (isset($this->curl->response->status) && $this->curl->response->status === 500) {
                $url = $this->auth($truelocalAccount);
                $this->editAccount($truelocalAccount, $business);
            }
        } else {
            throw new OAuthCompanyException($this->message);
        }
    }


    private function getToken($content)
    {
        return $content->passToken;
    }

    private function getMyProfile($content)
    {
        return $content->myProfile;
    }

    private function getCompanyId($content, $str)
    {
        $businessId = null;
        if (strpos($content, $str)) {
            $first_step = explode($str, $content);
            $second_step = explode('">', $first_step[1]);
            $businessId = str_replace('"', '', $second_step[0]);
        }
        return $businessId;
    }

    public function saveCookies($prefix, $cookies)
    {
        $dir = 'cookies';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $cookiesFile = fopen($dir . '/cookies_' . $prefix . '.txt', 'w');
        fwrite($cookiesFile, json_encode($cookies));
        fclose($cookiesFile);
    }

    public function createServiceAccount($data, BusinessInfo $business)
    {
        $this->curl->setHeaders(['content-type' =>  'application/json']);
        $params['displayName'] = $data['truelocal']['name'];
        $params['email'] = $data['truelocal']['email'];
        $params['password'] = $data['truelocal']['userPassword'];
        $params['confirmPassword'] = $data['truelocal']['userPassword'];
        $this->curl->post($this->createAccount, json_encode($params));
        if ($this->curl->response->meta->httpCode === 400) {
            throw new OAuthCompanyException(json_encode($this->curl->response->meta->errors));
        }
    }
}