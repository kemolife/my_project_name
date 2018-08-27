<?php

namespace SingAppBundle\Controller;

use Curl\Curl;
use Serps\Core\Browser\Browser;
use Serps\Core\Http\StackingHttpClient;
use Serps\Core\Serp\ItemPosition;
use Serps\HttpClient\CurlClient;
use Serps\SearchEngine\Google\GoogleClient;
use Serps\SearchEngine\Google\GoogleUrl;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\InstagramBusiness;
use SingAppBundle\Services\FacebookService;
use SingAppBundle\Services\GoogleService;
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
        $googleService = $this->get('app.hotfrog.service');
        var_dump($googleService->searchBusiness(new BusinessInfo()));
        die;
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
    public function testConnectService(Request $request)
    {
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');

        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
        return $this->redirect($googleService->authYoutube());
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
