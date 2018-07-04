<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Service\FacebookService;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class FacebookController extends BaseController
{
    /**
     * @Route("/facebook/auth", name="facebook-auth")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var FacebookService $facebookService
         */
        $facebookService = $this->get('app.facebook.service');

        return $this->redirect($facebookService->auth());
    }

    /**
     * @Route("/facebook/oauth2callback", name="facebook-oauth2callback")
     */
    public function oauth2callbackAction(Request $request)
    {
        /**
         * @var FacebookService $facebookService
         */
        $facebookService = $this->get('app.facebook.service');

        $accessTokeData = $facebookService->getAccessToken();

        $facebookService->createFacebookAccount($request, $accessTokeData);

        return $this->redirectToRoute('social-network-posts');
    }

}