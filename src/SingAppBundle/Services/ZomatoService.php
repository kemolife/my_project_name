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
                'cookie' => 'ak_bmsc=1D5BBDEAC86708D1F077C9773E723D9968513C953706000033BE5C5B87B4751B~plH1+1DU5Wons/c/1q3K8iUqBL0YHt3VTbzRZPDK5YF0yLh8+k+OXIVnlOP579SfzNrmI3EjX7CRjz84TJYk3MYG1ra92Gae82XamBeufLIIfLqaiVwoRmXe4qUvmwbiMEVilHvVJKVknIOtqeWguBRibrWnJLe/eypA4TW+YGAAlB2o0jHoJfD3h+5qrptHeeYaoF4SDanLYySue4xnLeRmYiBLVYd9FZZpj/s232BQ8=; _ga=GA1.2.1717303302.1532804660; _gid=GA1.2.1918272120.1532804660; _gat_country=1; _gat_global=1; __utmx=141625785.FQnzc5UZQdSMS6ggKyLrqQ$0:-1; __utmxx=141625785.FQnzc5UZQdSMS6ggKyLrqQ$0:1532804659:8035200; fbtrack=6532b9465992b-ac41-4b2e-b209-fb4a3e64942a; session_id=2532a44659934-5ca1-4d64-9c26-69c01eeebf6b; AMP_TOKEN=%24NOT_FOUND; _gat_globalV3=1; fbcity=267; dpr=1; cto_lwid=67977408-078a-4b8d-a4f5-781d89fa390a; PHPSESSID=962de5f9145e6022906733faf8bc32922c9439de; csrf=ab0bd9be6de54d84142fe1058adef231; zl=pl; bm_sv=0428D2747FB1DA3442E4083D3D4164E0~Cf4o/+zcY4WM7LlXwCK8GPv0TlGrT5PX8oQQnIEbtmV6liRR1tv6B79BnWEBsDH8LHpyjPY5/chAv4mxbOU5lb0PjVRGNuO6uIgcil9mjJ1bZCtE8KHlPrDamiZIgfrxM8PTBqzVit9yu2t3I+fGy7/lKXcHFjzriR1I6BiswNk=; G_ENABLED_IDPS=google'
            ]);
            $params['login'] = $zomatoAccount->getUserEmail();
            $params['password'] = $zomatoAccount->getUserPassword();
            $this->curl->post('https://www.zomato.com/php/asyncLogin.php', $params);
            $response = $this->curl->response;
            if(!isset($response->user_id)){
                throw new OAuthCompanyException('check your credential, or try again later');
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
        $yelp = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $yelp;
    }

    public function editAccount(ZomatoAccount $zomatoAccount)
    {
        $fileName = $this->webDir . '/cookies/cookies_' . $zomatoAccount->getUserEmail() . '.txt';

        if (file_exists($fileName) && $zomatoAccount instanceof ZomatoAccount) {
            $cookies = json_decode(file_get_contents($fileName), true);
            $prepareCookie = $cookies;
            array_walk($prepareCookie, function (&$value, $key) {
                $value = "{$key}={$value};";
            });
            $this->curl->setHeaders(['cookie' => implode($prepareCookie)]);
            $this->curl->get('https://www.zomato.com/pl/users/' . $zomatoAccount->getUserServiceId() . '/edit');
            var_dump($this->curl->response); die;
        }else{
            throw new OAuthCompanyException('please try again later');
        }
    }

    private function getCSRFToken($content, $inputName)
    {

        if ($inputName == 'meta') {
            $first_step = explode('<meta name="csrf-token"', $content);
            var_dump($first_step);
            $second_step = explode(' />', $first_step[1]);
            $csrf = str_replace('"', '', $second_step[0]);
        } else {
            $first_step = explode($inputName, $content);
            var_dump($first_step);
            die;
            $second_step = explode('value="', $first_step[2]);
            $third_step = explode('">', $second_step[1]);
            $csrf = str_replace('"', '', $third_step[0]);
        }


        return $csrf;
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