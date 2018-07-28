<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\ZomatoAccount;

class ZomatoService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth(ZomatoAccount $zomatoAccount)
    {
        $this->connect($zomatoAccount);
    }

    public function createAccount(ZomatoAccount $zomatoAccount)
    {
        $createdDate = new \DateTime();

        $zomatoAccount->setCreated($createdDate);

        $this->em->persist($zomatoAccount);
        $this->em->flush();
    }

    public function getAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:ZomatoAccount');
        $yelp = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $yelp;
    }

    private function connect(ZomatoAccount $zomatoAccount)
    {
        $response = null;

        if ($zomatoAccount instanceof ZomatoAccount) {
            $cookies = [];

            $curl = new Curl();
            $curl->get('https://www.zomato.com/pl/users/56604252/edit');
            print_r($curl->response); die();
            $curl->setHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]);
            $params['login'] = $zomatoAccount->getUserEmail();
            $params['password'] = $zomatoAccount->getUserPassword();
            $curl->post('https://www.zomato.com/php/asyncLogin.php', $params);
            var_dump($params);
            var_dump($curl->getResponseCookies()); die;
            $csrf = $this->getCSRFToken($curl->response, 'meta');
            var_dump($csrf); die;
            $params['email_id'] = $zomatoAccount->getUserEmail();
            $params['login_url'] = '';
            $params['return_url'] = '';
            $params['password'] = $zomatoAccount->getUserPassword();
            $params['csrftok'] = $csrf;
            $curl->setCookies($curl->getResponseCookies());
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $curl->post('https://www.wordofmouth.com.au/sign_in', $params);
            if (strpos($curl->responseHeaders['location'], 'locations') > 0) {
                $cookies = array_merge($cookies, $curl->getResponseCookies());
                $curl->setCookies($cookies);
                $curl->get('https://www.wordofmouth.com.au/sign_in');
                $url_pattern = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
                var_dump($curl->response); die;

                if (preg_match($url_pattern, $curl->response, $matches)) {
                    $curl->setCookies($cookies);
                    $curl->get($matches[0]);

                    $dom = HtmlDomParser::str_get_html($curl->response);

                    $businesses = $dom->find('select[class=form-control locationDropdown]');
                    if ($businesses) {
                        $response = [];
                        foreach ($businesses[0]->children as $business) {
                            $response['businesses'][] = $business->getAttribute('value');
                        }
                        $this->saveCookies($credentials['username'], $cookies, 'listing');
                    }
                }
            }
        }

        return $response;
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
            var_dump($first_step); die;
            $second_step = explode('value="', $first_step[2]);
            $third_step = explode('">', $second_step[1]);
            $csrf = str_replace('"', '', $third_step[0]);
        }


        return $csrf;
    }
}