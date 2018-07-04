<?php

namespace SingAppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class YelpController extends BaseController
{
    /**
     * @Route("/yelp/auth", name="yelp-auth")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.yelp.service');

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

        $googleService->createGoogleAccount($request, $accessTokeData);

        return $this->redirectToRoute('social-network-posts');
    }

    /**
     * @Route("/google/location", name="google-location")
     */
    public function chooseLocationAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:GoogleAccount');

        $googleAccount = $repository->findOneBy(['business' => $this->getCurrentBusiness($request)->getId()]);

        if ($googleAccount instanceof GoogleAccount) {
            /**
             * @var GoogleService $googleService
             */
            $googleService = $this->get('app.google.service');

            $googleService->getLocations($googleAccount);
        }

        return $this->redirectToRoute('social-network-posts');
    }
}