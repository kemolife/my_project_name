<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FoursquareAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\FoursquareType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\FoursquareService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class FoursquareController extends BaseController
{
    /**
     * @Route("/foursquare", name="foursquare")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $foursquare = new FoursquareAccount();
        $foursquareService = $this->get('app.foursquare.service');
        $form = $this->createForm(FoursquareType::class, $foursquare);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var FoursquareService $foursquareService
             */
            try{
                $foursquareService->createAccount($currentBusiness, $foursquare);
                return $this->redirectToRoute('index');
            }catch (OAuthCompanyException $e){
                return $this->render('@SingApp/services-form/foursquare.html.twig', ['form' => $form->createView(), 'error' => 'Credential bad or try again later']);
            }
        }

        return $this->render('@SingApp/services-form/foursquare.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/auth/foursquare", name="foursquare-auth")
     */
    public function authAction(Request $request)
    {
        $foursquareService = $this->get('app.foursquare.service');
        $this->session->set('business', $request->get('business'));
        $this->session->set('url', $request->get('url'));
        return $this->redirect($foursquareService->auth());
    }

    /**
     * @Route("/foursquare/oauth2callback", name="foursquare-oauth2callback")
     */
    public function foursquareCallbackAction(Request $request)
    {
        var_dump($this->session->get('business')); die;
        /**
         * @var FoursquareService $foursquareService
         */
        $foursquareService = $this->get('app.foursquare.service');
        try {
            $accessTokeData = $foursquareService->getToken($request->get('code'));
            $foursquareService->createAccount($accessTokeData);
            return $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
        }catch (OAuthCompanyException $e){
            return $this->redirectToRoute($this->session->get('url'), ['error' => $e->getMessage(), 'business' => $this->session->get('business')]);
        }
//        $foursquareAccount = $foursquareService->getFoursquareSetting($user, $currentBusiness);
//        if(null === $foursquareAccount){
//            $foursquareAccount = $foursquareService->createFoursquareAccount($currentBusiness, $request->get('code'));
//        }
//        if($foursquareAccount->getCode() !== $request->get('code')){
//            $foursquareAccount = $foursquareService->updateFoursquareAccount($foursquareAccount, $request->get('code'));
//        }
//        if($foursquareService->getToken($foursquareAccount->getCode()) === 0){
//            return $this->redirect($foursquareService->auth());
//        }
//        try {
//            $foursquareService->getAndUpdatePrivateVenues($foursquareService->getToken($foursquareAccount->getCode()));
//            return $this->redirectToRoute('index', ['business' => $this->session->get('business')]);
//        }catch (OAuthCompanyException $e){
//
//        }
    }
}