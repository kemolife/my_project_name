<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FacebookAccount;
use SingAppBundle\Entity\Post;
use SingAppBundle\Services\FacebookService;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
        if (!$request->get('error_message')) {
            /**
             * @var FacebookService $facebookService
             */
            $facebookService = $this->get('app.facebook.service');

            $accessTokeData = $facebookService->getAccessToken();

            $facebookService->createFacebookAccount($request, $accessTokeData);

            $response = $this->redirectToRoute('facebook-pages', ['business' => $this->session->get('business')]);
        }else {

            $response = $this->redirectToRoute(($this->session->get('url')), ['business' => $this->session->get('business')]);
        }
        return $response;
    }

    /**
     * @Route("/facebook/pages", name="facebook-pages")
     * @Security("has_role('ROLE_USER')")
     */
    public function choosePageAction(Request $request)
    {
        $currentBusiness = $this->getCurrentBusiness($request);

        $repository = $this->getRepository('SingAppBundle:FacebookAccount');

        /**
         * @var FacebookAccount $facebookAccount
         */
        $facebookAccount = $repository->findOneBy(['user' => $this->getUser()->getId(), 'business' => $currentBusiness->getId()]);

        /**
         * @var FacebookService $facebookService
         */
        $facebookService = $this->get('app.facebook.service');

        $pages = $facebookService->getPages($facebookAccount);


        $params = [
            'pages' => $pages,
            'facebookAccount' => $facebookAccount,
            'businesses' => $this->getBusinesses()
        ];

        return $this->render('@SingApp/services-form/facebook-location.html.twig', $params);
    }

    /**
     * @Route("/facebook/page", name="facebook-choose-page")
     * @Security("has_role('ROLE_USER')")
     */
    public function choosePage(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:FacebookAccount');

        $facebookAccount = $repository->findOneBy(['business' => $this->getCurrentBusiness($request)]);
        $em = $this->getDoctrine()->getManager();

        $facebookAccount->setPage($request->get('page'));
        $facebookAccount->setPageAccessToken($request->get('pageAccessToken'));

        $em->persist($facebookAccount);

        /**
         * @var FacebookService $facebookService
         */
        $facebookService = $this->get('app.facebook.service');
        $facebookService->setPosts($facebookAccount);

        $em->flush();

        return $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
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

    /**
     * @Route("/facebook/facebook-delete/{post}", name="facebook-delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function facebookPostDeletePostAction(Post $post, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('facebook-post', $request->query->all());
    }

}