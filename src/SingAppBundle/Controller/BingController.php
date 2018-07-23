<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FoursquareAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\FoursquareType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\BingService;
use SingAppBundle\Services\FoursquareService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class BingController extends BaseController
{
    /**
     * @Route("/auth/bing", name="bing-auth")
     */
    public function authAction(Request $request)
    {
        /**
         * @var BingService $bingService
         */
        $bingService = $this->get('app.bing.service');
        $this->session->set('url', $request->get('url'));
        return $this->redirect($bingService->auth());
    }

    /**
     * @Route("/bing/oauth2callback", name="bing-oauth2callback")
     */
    public function bingCallbackAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        /**
         * @var BingService $bingService
         */
        $bingService = $this->get('app.bing.service');
        $accessTokeData = $bingService->getToken($request->get('code'));
        $bingService->createAccount($currentBusiness, $accessTokeData);
        return $this->redirectToRoute($this->session->get('url'));
    }
}