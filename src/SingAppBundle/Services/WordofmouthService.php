<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\WordofmouthAccount;

class WordofmouthService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function auth(WordofmouthAccount $wordofmouthAccount)
    {
        $this->connect($wordofmouthAccount);
    }

    public function createAccount(WordofmouthAccount $wordofmouthAccount)
    {
        $createdDate = new \DateTime();

        $wordofmouthAccount->setCreated($createdDate);

        $this->em->persist($wordofmouthAccount);
        $this->em->flush();
    }

    public function getAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:WordofmouthAccount');
        $yelp = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $yelp;
    }

    /**
     * @return EntityManagerInterface
     */
    private function connect(WordofmouthAccount $wordofmouthAccount)
    {
        $response = null;

        if ($wordofmouthAccount instanceof WordofmouthAccount) {
            $cookies = [];

            $curl = new Curl();
            $curl->setHeaders([
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                'Host' => 'www.wordofmouth.com.au',
                'Referer' => 'https://www.wordofmouth.com.au/sign_up',
                'Upgrade-Insecure-Requests' => 1,
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
            ]);
//            $curl->setCookies('_ga=GA1.3.767692997.1532595095; fbm_2496339739=base_domain=.wordofmouth.com.au; D_IID=4C542220-D1E6-30A6-A3F8-F2AF0EA86969; D_UID=26406855-6DA1-30B9-B11A-F3A47E053FD9; D_ZID=56B56AF1-D26A-32EA-AEBC-0F839051CB47; D_ZUID=65C020FD-CB3F-38F1-A929-24416FC21700; D_HID=3CC8B4EB-82A7-3C75-8DC3-D367B1447820; D_SID=194.44.220.38:0pF7H/vBO7J407wqNuE3Zde6ao36ma3nhs4FM1b9W40; __zlcmid=nahWDcEEm6McTQ; _gid=GA1.3.361625944.1532693147; _sp_ses.341e=*; fbsr_2496339739=GrkZ-lSxJ8eHby-NsmVJGGy5thBOFauUB1KTWza48Zg.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUJzeUR3TjlFYWVlLXltdlhfbk9KbFh1a0xHUEd0V1FXN2JUanVaZkRaSlY4YS1FendpVUlUSXhrUXlmWTFUZlczdGMtVDJuOTM1RGotek5rUE5SbFlpM0FxXzBmSkFMYmdUQlpGd1VjY29Tb3JMNGJvRWt4SVM0UnhRQXUybjJmQmo1UXMwWjR0R0VWRFdCdHBDWmM4LXhiWGZXd3ZJZFltN2VRbnY2S1B5d0ZzT2lXd0t3bHRwLU5hQkdnbngwZmtQMVJUekYyRVg1dElaV3R3cmFIUUpLaXdGdUtraVhaSU9uU21yMm0zUmtPYUFoTnA5S1BIRFBjQW5hNFFrbzVqY0w2Z0RfNFBqNTRxQXVHN0JzVlRwN2FSUWxPc1VCV2QwRTlRZVhSWjZtXzlIYUJGRTJmekdzY2NqeG5Cc24xeC1ubG1LTl96a2xaaUNDYTRvTjc3MCIsImlzc3VlZF9hdCI6MTUzMjY5NTAxMywidXNlcl9pZCI6IjIxNjAyNDcyNDA4NzE4NzEifQ; _sp_id.341e=427643e7-5ebb-41dd-a224-55c8bf3f2bf7.1532595077.2.1532695591.1532599972.f32fad1e-1b24-4637-84b4-f1d9f08e921f; _womo_session=U2x0VlhoSkc1TjV3bXVTb25Odzl1QXhvckdpZ0lqTTdDR3IyTVFTZ0txRkJBZFBMbE1OWnJ5V1Ewb2MwOG5Ka0Z5VGxLQVh4THZCZzgyWnF5d1hLQVMrLzlIK1Zoa0d1bTZCTTk2NVFvT2tOSDhKWmpzQlVQTzJEZmYxVDZiZ2ZWQ1BBRWtVVys5SFJtWGRLcDJjLytnPT0tLU9sN0RCNWNEZll5U2FLY1BBc21FTlE9PQ%3D%3D--e2b68988de8cff0e339128dd875a607299583ad8');
            $curl->get('https://www.wordofmouth.com.au/sign_in');
            print_r($curl->response); die;
            $csrf = $this->getCSRFToken($curl->response, 'meta');
            var_dump($csrf); die;
            $params['email_id'] = $wordofmouthAccount->getUserEmail();
            $params['login_url'] = '';
            $params['return_url'] = '';
            $params['password'] = $wordofmouthAccount->getUserPassword();
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