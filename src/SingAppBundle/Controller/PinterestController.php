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
        return $this->redirect($pinterestService->auth());
    }

    /**
     * @Route("/pinterest/oauth2callback", name="pinterest-oauth2callback", schemes={"https"})
     */
    public function pinterestCallbackAction(Request $request)
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
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        $accessTokeData = $pinterestService->getToken($request->get('code'));
        $pinterestService->createAccount($currentBusiness, $accessTokeData);
        return $this->redirectToRoute($this->session->get('url'));
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