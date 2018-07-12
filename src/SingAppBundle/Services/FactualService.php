<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;

class FactualService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth()
    {
        $yelpSetting = true;
        $this->yelpConnect($yelpSetting);
    }

    public function createAccount(BusinessInfo $business, YelpAccount $yelp)
    {
        $createdDate = new \DateTime();

        $yelp->setCreated($createdDate);
        $yelp->setBusiness($business);

        $this->em->persist($yelp);
        $this->em->flush();
    }

    private function getYelpSetting(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:YelpAccount');
        $yelp = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $yelp;
    }

    /**
     * @return EntityManagerInterface
     */
    private function yelpConnect($yelpSetting)
    {
        $response = null;

        if ($yelpSetting) {
            $cookies = [];

            $curl = new Curl();
            $curl->setHeaders([
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'referer' => 'https://biz.yelp.com/',
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36'
            ]);
            $curl->get('https://accounts.factual.com/login');
            $csrf = $this->getCSRFToken($curl->response, 'authenticity_token');
            $params['utf8'] = '✓';
            $params['user_session']['redirect_to'] = '';
            $params['authenticity_token'] = $csrf;
            $params['user_session']['email'] = 'customerservice@cubeonline.com.au';
            $params['user_session']['password'] = 'CubeOnline1!';
            $params['commit'] = 'Login';
            $curl->setCookies($curl->getResponseCookies());
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $curl->post('https://accounts.factual.com/login', $params);
            if (strpos($curl->response, 'logout') !== false) {
                $cookies = array_merge($cookies, $curl->getResponseCookies());
                $curl->setCookies($cookies);
                $curl->get('https://my.factual.com/update_add_business');
                print_r($curl->response); die;
                $this->saveCookies($credentials['username'], $cookies);
            }
        }

        return $response;
    }

    private function getCSRFToken($content, $inputName)
    {

        if ($inputName == 'meta') {
            $first_step = explode('<meta name="csrf-token" content="', $content);
            $second_step = explode(' />', $first_step[1]);
            $csrf = str_replace('"', '', $second_step[0]);
        } else {
            $first_step = explode($inputName, $content);
            $second_step = explode('value="', $first_step[2]);
            $third_step = explode(' />', $second_step[1]);
            $csrf = str_replace('"', '', $third_step[0]);
        }


        return $csrf;
    }

    private function saveCookies($prefix, $cookies)
    {
        $dir = 'cookies/factual';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $cookiesFile = fopen($dir . '/cookies_' . $prefix . '.txt', 'w');
        fwrite($cookiesFile, json_encode($cookies));
        fclose($cookiesFile);
    }
}