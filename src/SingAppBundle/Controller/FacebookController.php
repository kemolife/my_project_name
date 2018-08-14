<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Services\FacebookService;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class FacebookController extends BaseController
{

    const SERVICE_NAME = 'facebook';
    const SERVICE_MASSAGE = '';
    /**
     * @Route("/facebook/auth", name="facebook-auth")
     */
    public function indexAction(Request $request)
    {
        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
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

        return $this->redirectToRoute(($this->session->get('url')), ['business' => $this->session->get('business')]);
    }

    /**
     * @Route("/facebook/post", name="facebook-post")
     */
    public function postAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        $user = $this->getUser();

        $facebookAccount = $this->findOneBy('SingAppBundle:FacebookAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $facebookForm = $this->facebookPostForm($request)->createView();
        $facebookPosts = $posts = $this->findBy('SingAppBundle:Post', ['user' => $user->getId(), 'business' => $currentBusiness->getId(), 'socialNetwork' => self::SERVICE_NAME], ['postDate' => 'DESC']);

        $params = [
            'businesses' => $this->getBusinesses(),
            'form' => $facebookForm,
            'posts' => $facebookPosts,
            'account' => $facebookAccount,
            'service' => self::SERVICE_NAME,
            'massage' => self::SERVICE_MASSAGE,
            'currentBusiness' => $currentBusiness,
            'canDelete' => true
        ];

        return $this->render('@SingApp/socialNetworkPosts/index.html.twig', $params);
    }

}