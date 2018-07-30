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
        $handle = fopen($this->webDir .  "/cacert-2018-06-20.pem", "r");
        var_dump(fread($handle, filesize($this->webDir .  "/cacert-2018-06-20.pem")));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://www.zomato.com/pl/perth");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CAINFO, $this->webDir .  "/cacert-2017-09-20.pem");
        curl_exec($curl);
        var_dump(curl_error($curl)); die;
//        $curl->('https://www.zomato.com/pl/perth');
//        var_dump($this->curl->responseHeaders); die;
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

        return $zomatoAccount;
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
        return 'LEL_JS=true; zl=pl; _ga=GA1.2.1739363514.1531490815; dpr=1; zhli=1; frcp_g=1; cto_lwid=56aa1fba-8c69-42da-8344-aecb5d32005e; G_ENABLED_IDPS=google; __utmx=141625785.FQnzc5UZQdSMS6ggKyLrqQ$0:NaN; fbcity=296; al=0; orange=6973945; fbtrack=5532724475b66-3c82-402f-a3ce-fe0bda3b7d1e; csrf=4cd690d82bb79a495dfd139a8d9f823e; PHPSESSID=d71677826e00517bc873b7ee144cc1b4bb94524e; squeeze=9261975c5aa51f049ef65794d24ceade; ak_bmsc=5612D1FADD3DD3919D86536253E985CF68513C953706000080BE5E5B6AC20265~pl6W+IOJdgUMRYNL7VID3MLuH17PtvTfpix5IAlK8KO99UMdj+ss8oEo7Xaks4/leTIeS/Ry7mUA8wyyOpcNGbnrGUFudyZncBenAAp1MvSXtZap8TKRtjHjofsdJc0CiqBSfMZwTd4tnrnMQrdWZXew2fQHDSMQoGFufdzcUZtmH8+5bneA9oEk/pZ2fogQtg8jY5VZW+lScI4Z5NvuwsSOO1XGk3iaFvZFmcjyrF9FM=; _gid=GA1.2.1196688860.1532935809; _gat_city=1; _gat_country=1; _gat_global=1; __utmxx=141625785.FQnzc5UZQdSMS6ggKyLrqQ$0:1532935809:8035200; AMP_TOKEN=%24NOT_FOUND; _gat_globalV3=1; bm_sv=383A9D231605B81693BA01B31EE45044~Cf4o/+zcY4WM7LlXwCK8GEhVTyQts2Kmxf/7q1UwWMkIQJ0iIv3JwnVyDFGdwOBNP+NxMB5q0agUpnFTDxVizsHevjw4kczFELjkv8tquU2QdkymZFCsjoWuPM8q5ZibpOlt8LW9hd7YEvsOtwImgSnPhMh7AROS6hXqufzhcNw=';
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