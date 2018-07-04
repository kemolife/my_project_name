<?php

namespace SingAppBundle\Controller;

use FacebookAds\Http\Adapter\Curl\Curl;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Businessinfo controller.
 *
 * @Route("oauth")
 */
class OAuthController extends BaseController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        $currentBusiness = $this->getCurrentBusiness($request);
        $businessForm = $this->businessPostForm(new BusinessInfo(), $request)->createView();
        $businessFormEdit = $this->businessPostForm($currentBusiness, $request, true)->createView();
        $params = [
            'businesses' => $this->getBusinesses(),
            'businessForm' => $businessForm,
            'businessFormEdit' => $businessFormEdit,
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
     * @Route("/instagram-update", name="instagramUpdate")
     */
    public function testApi()
    {
        $em = $this->getDoctrine()->getManager();
        $additionalCategoryEntity = new AdditionalCategoriesBusinessInfo();
        $additionalCategoryEntity->setName('xxx');
        $em->persist($additionalCategoryEntity);
        $em->flush();
    }
}