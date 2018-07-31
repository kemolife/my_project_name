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
        $user = $this->getUser();
        /**
         * @var InstagramBusiness $instagram
         */
        $instagram = $this->get('instagram_provider');
        var_dump($instagram->newAuth($user, $currentBusiness)->getAllComments());
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
        $url = 'https://www.hotfrog.com/Login.aspx';
        $curl = new Curl();
        $params['__LASTFOCUS'] = '';
        $params['__EVENTTARGET'] = 'ctl00$contentSection$LoginButton';
        $params['__EVENTARGUMENT'] = '';
        $params['ctl00$hotFrogHeader$hotfrogSearch$txtWhat'] = 'test_business';
        $params['ctl00$hotFrogHeader$hotfrogSearch$txtWhere'] = '';
        $params['ctl00$contentSection$EmailAddress'] = 'kemolife1990@gmail.com';
        $params['ctl00$contentSection$Password'] = 'kemo2701';
        $params['ctl00$hotFrogFooter$hotfrogSearch$txtWhat'] = '';
        $params['ctl00$hotFrogFooter$hotfrogSearch$txtWhere'] = '';
        $params['ctl00$HiddenSocialUID'] = '';
        $curl->post($url, $params);
        $this->session->set('cookie', $curl->getResponseCookies());
    }

    /**
     * @Route("/test/update", name="test-update")
     */
    public function testUpdateBusiness()
    {
        $curl = new Curl();
        $curl->setHeaders([
            'accept' => 'https://www.hotfrog.com',
            'referer' => 'https://www.hotfrog.com/UpdateDetails.aspx?editSection=ContactDetails&CompanyID=43444136',
        ]);
        $params['__LASTFOCUS'] = '';
        $params['__EVENTTARGET'] = 'ctl00$contentSection$btnUpdate';
        $params['__EVENTARGUMENT'] = '';
        $params['ctl00$contentSection$ctrlContactDetails$txtBusinessName'] = 'test_business2';
        $params['ctl00$contentSection$ctrlContactDetails$hiddenX'] = '-85.250489';
        $params['ctl00$contentSection$ctrlContactDetails$hiddenY'] = '31.571835';
        $params['ctl00$contentSection$ctrlContactDetails$hiddenAccuracy'] = 4;
        $params['l00$contentSection$ctrlContactDetails$hiddenCountry'] = '';
        $params['ctl00$contentSection$ctrlContactDetails$txtBusinessName'] = 'test_business';
        $params['ctl00$contentSection$ctrlContactDetails$txtStreetAddress'] = 'Lvіv-poshtamt';
        $params['ctl00$contentSection$ctrlContactDetails$txtAddress2'] = '';
        $params['ctl00$contentSection$ctrlContactDetails$txtAddress3'] = '';
        $params['ctl00$contentSection$ctrlContactDetails$txtSuburb'] = 'Abbeville';
        $params['ctl00$contentSection$ctrlContactDetails$cboState'] = 'AL';
        $params['ctl00$contentSection$ctrlContactDetails$txtPostcode'] = '36310';
        $params['ctl00$contentSection$ctrlContactDetails$txtPhone'] = '+3809696903531';
        $params['ctl00$contentSection$ctrlContactDetails$txtFax'] = '';
        $params['ctl00$contentSection$ctrlContactDetails$txtWebsite'] = 'http://test.com.ua';
        $params['ctl00$contentSection$ctrlContactDetails$txtEmail'] = 'kemolife1990@gmail.com';
        $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 1;
        $curl->setCookies($this->session->get('cookie'));
        $curl->post('https://www.hotfrog.com/UpdateDetails.aspx?editSection=ContactDetails&CompanyID=43444136', $params);
        print_r($curl->response); die;
    }
}
