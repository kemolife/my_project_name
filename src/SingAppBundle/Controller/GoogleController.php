<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Services\GoogleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class GoogleController extends BaseController
{
    /**
     * @Route("/google/auth", name="google-auth")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');

        $this->session->set('url', $request->get('url'));
        return $this->redirect($googleService->auth());
    }

    /**
     * @Route("/google/oauth2callback", name="google-oauth2callback")
     */
    public function oauth2callbackAction(Request $request)
    {
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');

        $accessTokeData = $googleService->getAccessToken($request->get('code'));

        $googleService->createGoogleAccount($request, $accessTokeData, $this->getCurrentBusiness($request)->getId());

        return $this->redirectToRoute($this->session->get('url'));
    }

    /**
     * @Route("/google/location", name="google-location")
     */
    public function chooseLocationAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:GoogleAccount');

        $googleAccount = $repository->findOneBy(['business' => 1]);

        if ($googleAccount instanceof GoogleAccount) {
            /**
             * @var GoogleService $googleService
             */
            $googleService = $this->get('app.google.service');

            var_dump($googleService->getLocations($googleAccount)); die;
        }


        return $this->redirectToRoute('social-network-posts');
    }
}