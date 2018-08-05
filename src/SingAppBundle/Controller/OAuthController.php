<?php

namespace SingAppBundle\Controller;

use Curl\Curl;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\InstagramBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Businessinfo controller.
 *
 */
class OAuthController extends BaseController
{
    /**
     * @Route("/", name="index")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if(empty($this->getBusinesses())){
            return $this->addBusiness($request, $user);
        }
        $currentBusiness = $this->getCurrentBusiness($request);
//        $businessFormEdit = $this->businessPostForm($currentBusiness, $request, true, $user)->createView();
        $params = [
            'businesses' => $this->getBusinesses(),
//            'businessFormEdit' => $businessFormEdit,
            'currentBusiness' => $currentBusiness
        ];
        return $this->render('@SingApp/oauth/index.html.twig', $params);
    }

    /**
     * @Route("/save-token", name="saveToken")
     */
    public function actionSaveToken(Request $request)
    {
        $session = $request->getSession();
        $sessionValue = [];
        $sessionKey = '';
        foreach ($request->query->all() as $key => $item){
            if($key == 'service') {
                $sessionKey = $item;
            }else{
                $sessionValue[$key] = $item;
            };
        }
        $session->set($sessionKey, $sessionValue);

        return new Response();
    }

    /**
     * @Route("/test/api", name="test-api")
     */
    public function testApi(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var User $user
         */
        var_dump($currentBusiness->getLongitude());
        var_dump($currentBusiness->getCategory()->getName());
        var_dump($currentBusiness->getCategory()->getCategoryId());
        var_dump($currentBusiness->getAdditionalCategories());
        $user = $this->getUser();
        $period = [];
        foreach (\GuzzleHttp\json_decode($currentBusiness->getOpeningHours())->days as $key => $item) {
            if ($item->type === 'open') {
                $day = new \stdClass();
                $day->openDay = strtoupper($key);
                $day->openTime = $item->slots[0]->start;
                $day->closeDay = strtoupper($key);
                $day->closeTime = $item->slots[1]->end;
                array_push($period, $day);
            }
        }
        var_dump(\GuzzleHttp\json_encode($period));
//        /**
//         * @var InstagramBusiness $instagram
//         */
//        $instagram = $this->get('instagram_provider');
//        var_dump($instagram->newAuth($user, $currentBusiness)->getAllComments());
    }

    /**
     * @Route("/business/add", name="add-business")
     * @Security("has_role('ROLE_USER')")
     */
    public function addBusinessAction(Request $request)
    {
        $businessInfo = new Businessinfo();
        /**
         * @var User $user
         */
        $user = $this->getUser();
        return $this->businessPostForm($businessInfo, $request, false, $user);
    }

    /**
     * @Route("/business/edit", name="edit-business")
     * @Security("has_role('ROLE_USER')")
     */
    public function editBusinessAction(Request $request)
    {
        /**
         * @var User $user
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        $user = $this->getUser();
        return $this->businessPostForm($currentBusiness, $request, true, $user);
    }
    
    /**
     * @Route("/test/connect", name="test-connect")
     */
    public function testConnectService()
    {
        var_dump(trim('+61412123423', '+')); die;
        $url = 'https://api.truelocal.com.au/rest/auth/login?passToken=V0MxbDBlV2VNUw==';
        $curl = new Curl();
        $curl->setHeaders(
            [
               'content-type' =>  'application/json'
            ]
        );
        $params['email'] = 'kemolife1990@gmail.com';
        $params['password'] = 'kemo2701';
        $curl->post($url, '{"email":"kemolife1990@gmail.com","password":"kemo2701"}');
        var_dump($curl->response); die;
        $this->session->set('cookie', $curl->getResponseCookies());
    }

    /**
     * @Route("/test/update", name="test-update")
     */
    public function testUpdateBusiness()
    {
        $curl = new Curl();
        $curl->setHeaders(['content-type' =>  'application/json']);
        $curl->setCookies($this->session->get('cookie'));
        $curl->post('https://api.truelocal.com.au/rest/users/3F4CDD76-A06B-4851-94C3-D659E434CB4A/update?passToken=V234DBlV2VNUzo2NDMyMzAwNjhhY2QzZDYwOTEyMWFlZWVkNGM4ZjlkZDgzMzMxYzc1NzZiNjYzYWNhYzhjNGU0ZTkyZmYyMjE4',
            '{"displayName":"vitalii antoniuk12","address":{"suburb":"Rosebery","postCode":"3395","state":"VIC"},"description":"test12","firstName":"kemolife1990","lastName":"Pissas","phoneNumber":"0435546567","hideSuburb":false}');
        var_dump($curl->response); die;

    }

}
