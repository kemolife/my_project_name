<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\ZomatoAccount;
use SingAppBundle\Providers\Exception\OAuthCompanyException;

class ZomatoService
{
    private $em;
    private $curl;
    private $webDir;
    /**
     * @var BusinessInfo
     */
    private $business;
    private $message = 'zomato service error, please connect to technical advice';

    /**
     * ZomatoService constructor.
     * @param EntityManagerInterface $entityManager
     * @param $webDir
     * @throws \ErrorException
     */
    public function __construct(EntityManagerInterface $entityManager, $webDir)
    {
        $this->em = $entityManager;
        $this->webDir = $webDir;
        $this->curl =  $curl = new Curl();
    }

    /**
     * @param ZomatoAccount $zomatoAccount
     * @return mixed
     * @throws OAuthCompanyException
     */
    public function auth(ZomatoAccount $zomatoAccount)
    {
        $response = null;

        if ($zomatoAccount instanceof ZomatoAccount) {

            $this->curl->setHeaders([
                'cookie' => $this->getCookies()
            ]);
            $params['login'] = $zomatoAccount->getUserEmail();
            $params['password'] = $zomatoAccount->getUserPassword();
            $this->curl->post('https://www.zomato.com/php/asyncLogin.php', $params);
            if(!isset($response->user_id)){
                throw new OAuthCompanyException($this->message);
            }
            $this->saveCookies('zomato_'. $zomatoAccount->getUserEmail(), $this->curl->getResponseCookies());
        }
        return $response->user_id;
    }

    /**
     * @param ZomatoAccount $zomatoAccount
     * @param $userServiceId
     */
    public function createAccount(ZomatoAccount $zomatoAccount, $userServiceId)
    {
        $createdDate = new \DateTime();

        $zomatoAccount->setCreated($createdDate);
        $zomatoAccount->setUserServiceId($userServiceId);

        $this->em->persist($zomatoAccount);
        $this->em->flush();
    }

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @return null|object
     */

    public function getAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:ZomatoAccount');
        $zomato = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $zomato;
    }

    /**
     * @param ZomatoAccount $zomatoAccount
     * @throws OAuthCompanyException
     */
    public function editAccount(ZomatoAccount $zomatoAccount, BusinessInfo $business)
    {
        $this->business = $business;
        $fileName = $this->webDir . '/cookies/cookies_zomato_' . $zomatoAccount->getUserEmail() . '.txt';
        if (file_exists($fileName) && $zomatoAccount instanceof ZomatoAccount) {
            $cookies = json_decode(file_get_contents($fileName), true);
            $prepareCookie = $cookies;
            array_walk($prepareCookie, function (&$value, $key) {
                $value = "{$key}={$value};";
            });
            $this->curl->setHeaders(['cookie' => implode($prepareCookie)]);
            $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $this->curl->get('https://www.zomato.com/pl/users/' . $zomatoAccount->getUserServiceId() . '/edit');
            if($this->curl->response){
                $csrf = $this->getCSRFToken($this->curl->response, 'csrf_token', $zomatoAccount, $business);
                $params['edit-profile-form-submitted'] = 1;
                $params['csrf_token'] = $csrf;
                $params['name'] = $this->business->getName();
                $params['city'] = 3057;
                $params['bio'] = $this->business->getDescription();;
                $params['twitter_handle'] = '';
                $params['language'] = 1;
                $params['website_link'] = $this->business->getWebsite();;
                $params['mobile'] = $this->business->getPhoneNumber();
                $params['submit'] = 'Zapisz';
                $this->curl->setHeaders(['cookie' => implode($prepareCookie)]);
                $this->curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
                $this->curl->post('https://www.zomato.com/pl/users/'.$zomatoAccount->getUserServiceId().'/edit', $params);
                if($this->curl->httpStatusCode !== '200'){
                    throw new OAuthCompanyException($this->message);
                };
            }else{
                $this->auth($zomatoAccount);
                $this->editAccount($zomatoAccount, $business);
            }
        }else{
            throw new OAuthCompanyException($this->message);
        }
    }

    /**
     * @param $content
     * @param $inputName
     * @param ZomatoAccount $zomatoAccount
     * @return mixed
     * @throws OAuthCompanyException
     */
    private function getCSRFToken($content, $inputName, ZomatoAccount $zomatoAccount, $business)
    {

        if ($inputName == 'meta') {
            $first_step = explode('<meta name="csrf-token"', $content);
            var_dump($first_step);
            $second_step = explode(' />', $first_step[1]);
            $csrf = str_replace('"', '', $second_step[0]);
        } else {
            $first_step = explode($inputName, $content);
            if(!isset($first_step[2])){
                $this->auth($zomatoAccount);
                $this->editAccount($zomatoAccount, $business);
            }
            $second_step = explode('value="', $first_step[2]);
            $third_step = explode('" />', $second_step[1]);
            $csrf = str_replace('"', '', $third_step[0]);
        }


        return $csrf;
    }

    private function getCookies()
    {
        return 'fbcity=267; zl=pl; fbtrack=5cd25883e54c93fcd8c2d63e77f7aefd; _ga=GA1.2.243531173.1532788938; _gid=GA1.2.2083606592.1532788938; __utmx=141625785.FQnzc5UZQdSMS6ggKyLrqQ$0:NaN; dpr=1; cto_lwid=e73b8097-e200-4a2f-b8ab-c88fcff59317; G_ENABLED_IDPS=google; zhli=1; al=0; PHPSESSID=1776864e23458b90c947617bd837858468605135; session_id=8532863259909-5b4d-43f1-95bf-765acf2dbffc; AMP_TOKEN=%24NOT_FOUND; ak_bmsc=DDF61A1B1081BF77B38119CF66E89EDF68513C953706000046D45D5BCF42397F~plQAwOb4S0w+/1YwljwIR5YtyIU5A1snxxQicz1q0oBajPdKOLQLegClymCqDmuIcmY7+1Ay+fS+jhPpE8F87IRoOHXTuRpjEqf+xficO2+TJdTb9303n7Js7a5VPfSIHCFiozpU5JTKowLlEJ4KWWfW0OTiL7YczwRAAx2rnnvZiCgpZQlfM5+luiN7c2xmbaAnz25/3y36Wc3regrXavc9vCGSZVWxL/w7dUMb2tx+c=; _gat_global=1; _gat_country=1; _gat_globalV3=1; __utmxx=141625785.FQnzc5UZQdSMS6ggKyLrqQ$0:1532865053:8035200; csrf=f384ac6f2f9843d34c1c0cba76c7ca49; bm_sv=517B4119E96261A343E301C9B3F17644~Cf4o/+zcY4WM7LlXwCK8GKlfpJiPRfhvci4VJMQmvLS8QpNa1XblDICyYxOS6CZtC/gBVRuO7GzkM1ZIeR//f8thR0hvtxqNZ3OPEMQJni1DoLO33bkpcnVsw9GxuQWARcLQzBrNHYylIbCmud/IyiEH7QwtkhLtmhZEwtuV4r8=';
    }

    private function saveCookies($prefix, $cookies)
    {
        $dir = 'cookies';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $cookiesFile = fopen($dir . '/cookies_' . $prefix . '.txt', 'w');
        fwrite($cookiesFile, json_encode($cookies));
        fclose($cookiesFile);
    }
}