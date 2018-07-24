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
        $this->session->set('business', $request->get('business'));
        return $this->redirect($bingService->auth());
    }

    /**
     * @Route("/bing/oauth2callback", name="bing-oauth2callback")
     */
    public function bingCallbackAction(Request $request)
    {
        /**
         * @var BingService $bingService
         */
        try {
            $bingService = $this->get('app.bing.service');
            $accessTokeData = $bingService->getToken($request->get('code'));
            $bingService->createAccount($accessTokeData);
            return $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
        }catch (OAuthCompanyException $e){
            return $this->redirectToRoute($this->session->get('url'), ['error' => $e->getMessage(), 'business' => $this->session->get('business')]);
        }
    }

    /**
     * @Route("/bing/owner", name="bing-owner")
     */
    public function getOwner(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var BusinessInfo $business
         */
        $business = $this->getCurrentBusiness($request);
        /**
         * @var BingService $bingService
         */
        $bingService = $this->get('app.bing.service');
        $account = $bingService->getBingAccount($user, $business);
        try {
            var_dump($bingService->setTokenObject($account)->getOwner());
        }catch (\Exception $e){
            var_dump($e->getMessage());
        }
    }
}