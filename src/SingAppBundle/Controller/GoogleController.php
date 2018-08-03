<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
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
        $this->session->set('business', $request->get('business'));
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

        $googleService->createUpdateGoogleAccount($accessTokeData);

        return $this->redirectToRoute('google-location', ['business' => $this->session->get('business')]);
    }

    /**
     * @Route("/google/location", name="google-location")
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
}