<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\PinterestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PinterestController extends BaseController
{
    /**
     * @Route("/auth/pinterest", name="pinterest-auth")
     */
    public function authAction(Request $request)
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
        return $this->redirect($pinterestService->auth());
    }

    /**
     * @Route("/pinterest/oauth2callback", name="pinterest-oauth2callback")
     */
    public function pinterestCallbackAction(Request $request)
    {
        /**
         * @var PinterestService $pinterestService
         */
        try {
            $pinterestService = $this->get('app.pinterest.service');
            $accessTokeData = $pinterestService->getToken($request->get('code'));
            $pinterestService->createAccount($accessTokeData);
            return $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
        }catch (OAuthCompanyException $e){
            return $this->redirectToRoute($this->session->get('url'), ['error' => $e->getMessage(), 'business' => $this->session->get('business')]);
        }
    }

    /**
     * @Route("/pinterest-test", name="pinterest-test")
     */
    public function testAction()
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        $pinterestService->getAndUpdatePrivateVenues($pinterestService->getToken('c853c2bcdd30ae86'));
    }
}