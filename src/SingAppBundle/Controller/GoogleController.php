<?php


namespace SingAppBundle\Controller;

use Google_Exception;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\Post;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\GoogleService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class GoogleController extends BaseController
{
    /**
     * @Route("/google/auth", name="google-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');

        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
        return $this->redirect($googleService->auth());
    }

    /**
     * @Route("/google/oauth2callback", name="google-oauth2callback")
     * @Security("has_role('ROLE_USER')")
     */
    public function oauth2callbackAction(Request $request)
    {
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');

        $accessTokeData = $googleService->getAccessToken($request->get('code'));

        $googleService->createUpdateGoogleAccount($accessTokeData);

        return $this->redirectToRoute('google-location', ['business' => $this->session->get('business')]);
    }

    /**
     * @Route("/google/location", name="google-location")
     * @Security("has_role('ROLE_USER')")
     */
    public function chooseLocationAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:GoogleAccount');

        $googleAccount = $repository->findOneBy(['business' => $this->getCurrentBusiness($request)]);

        if ($googleAccount instanceof GoogleAccount) {
            /**
             * @var GoogleService $googleService
             */
            $googleService = $this->get('app.google.service');

            $accounts = $googleService->getAccountsLocations($googleAccount);

            return $this->render('@SingApp/services-form/google-location.html.twig', ['accounts' => $accounts, 'businesses' => $this->getBusinesses()]);

        }


        return $this->redirectToRoute('social-network-posts');
    }

    /**
     * @Route("/google/create/location", name="google-crete-location")
     * @Security("has_role('ROLE_USER')")
     */
    public function createLocationAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:GoogleAccount');

        $googleAccount = $repository->findOneBy(['business' => $this->getCurrentBusiness($request)]);

        if ($googleAccount instanceof GoogleAccount) {
            /**
             * @var BusinessInfo $currentBusiness
             */
            $currentBusiness = $this->getCurrentBusiness($request);
            /**
             * @var GoogleService $googleService
             */
            $googleService = $this->get('app.google.service');
            try {
                $location = $googleService->createLocation($googleAccount, $request->get('account'), $currentBusiness);
                $googleService->updateAccountLocation($googleAccount, $location->name);
            }catch (OAuthCompanyException $e){
                return $this->redirectToRoute('google-location', [
                    'accounts' => $googleAccount,
                    'businesses' => $this->getBusinesses(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $this->redirectToRoute($this->session->get('url'), ['businesses' => $this->getBusinesses()]);
    }

    /**
     * @Route("/google/choose/location", name="google-choose-location")
     * @Security("has_role('ROLE_USER')")
     */
    public function chooseLocationAccountAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:GoogleAccount');

        $googleAccount = $repository->findOneBy(['business' => $this->getCurrentBusiness($request)]);
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');
        if ($googleAccount instanceof GoogleAccount) {
            $googleService->updateAccountLocation($googleAccount, $request->get('location'));
            try {
                $googleService->updateLocation($googleAccount,  $request->get('location'), $currentBusiness);
                $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
            }catch (OAuthCompanyException $e) {
                $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business'), 'error' => $e->getMessage().' Please update your business ']);
            }
        }

        return $this->redirectToRoute(($this->session->get('url')), ['business' => $this->session->get('business')]);
    }

    /**
     * @Route("/google/post-delete/{post}", name="google-post-delete")
     * @Security("is_granted('ABILITY_GOOGLE_POST_DELETE', post)")
     */
    public function googlePostDeletePostAction(Post $post, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($post);
        $em->flush();

        return $this->redirect($this->generateUrl('social-network-posts'));
    }

    /**
     * @Route("/google/reviews/reply", name="google-reviews-reply")
     * @Security("has_role('ROLE_USER')")
     */
    public function googleReviewsReplyAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        /**
         * @var GoogleService $googleService
         */
        $googleService = $this->get('app.google.service');

        $googleAccount = $this->findOneBy('SingAppBundle:GoogleAccount', ['user' => $this->getUser()->getId(), 'business' => $currentBusiness->getId()]);

        try{
            $googleService->reply($googleAccount, $request->get('reviewId'), $request->get('text'));
            return $this->redirectToRoute('interactions', ['business' => $request->query->all()['business']]);
        }catch (Google_Exception $e){
            return $this->redirectToRoute('interactions', ['business' => $request->query->all()['business'], 'error' => $e->getMessage()]);
        }
    }
}