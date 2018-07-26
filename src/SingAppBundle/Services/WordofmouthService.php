<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\YelpAccount;

class WordofmouthService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth(User $user, BusinessInfo $business)
    {
        $yelpSetting = $this->getYelpSetting($user, $business);
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
    private function yelpConnect(YelpAccount $yelpSetting)
    {
        $response = null;

        if ($yelpSetting !== null) {
            $cookies = [];

            $curl = new Curl();
            $curl->setHeaders([
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'referer' => 'https://biz.yelp.com/',
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36'
            ]);
            $curl->get('https://biz.yelp.com/login');
            $csrf = $this->getCSRFToken($curl->response, 'csrftok');
            $params['email_id'] = $yelpSetting->getUserEmail();
            $params['login_url'] = '';
            $params['return_url'] = '';
            $params['password'] = $yelpSetting->getUserPassword();
            $params['csrftok'] = $csrf;
            $curl->setCookies($curl->getResponseCookies());
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $curl->post('https://biz.yelp.com/login', $params);
            if (strpos($curl->responseHeaders['location'], 'locations') > 0) {
                $cookies = array_merge($cookies, $curl->getResponseCookies());
                $curl->setCookies($cookies);
                $curl->get('https://biz.yelp.com/');
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
            $first_step = explode('<meta name="csrf-token" content="', $content);
            $second_step = explode(' />', $first_step[1]);
            $csrf = str_replace('"', '', $second_step[0]);
        } else {
            $first_step = explode($inputName, $content);
            $second_step = explode('value="', $first_step[2]);
            $third_step = explode('">', $second_step[1]);
            $csrf = str_replace('"', '', $third_step[0]);
        }


        return $csrf;
    }
}