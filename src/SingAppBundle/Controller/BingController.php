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
//    /**
//     * @Route("/bing", name="bing")
//     * @Security("has_role('ROLE_USER')")
//     */
//    public function indexAction(Request $request)
//    {
//        /**
//         * @var BusinessInfo $currentBusiness
//         */
//        $currentBusiness = $this->getCurrentBusiness($request);
//        /**
//         * @var User $user
//         */
//        $user = $this->getUser();
//
//        $bing = new BingAccount();
//        $bingService = $this->get('app.bing.service');
//        $form = $this->createForm(BingType::class, $bing);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            /**
//             * @var bingService $bingService
//             */
//            try{
//                $bingService->createAccount($currentBusiness, $bing);
//                return $this->redirectToRoute('index');
//            }catch (OAuthCompanyException $e){
//                return $this->render('@SingApp/services-form/bing.html.twig', ['form' => $form->createView(), 'error' => 'Credential bad or try again later']);
//            }
//        }
//
//        return $this->render('@SingApp/services-form/bing.html.twig', ['form' => $form->createView()]);
//    }

    /**
     * @Route("/auth-bing", name="auth-bing")
     */
    public function authAction(Request $request)
    {
        /**
         * @var BingService $bingService
         */
        $bingService = $this->get('app.bing.service');
        return $this->redirect($bingService->auth());
    }

    /**
     * @Route("/bing-oauth2callback", name="bing-oauth2callback")
     */
    public function bingCallbackAction(Request $request)
    {
//        /**
//         * @var BusinessInfo $currentBusiness
//         */
//        $currentBusiness = $this->getCurrentBusiness($request);
//        /**
//         * @var User $user
//         */
//        $user = $this->getUser();



        $bingService = $this->get('app.bing.service');
//        $bingAccount = $bingService->getbingSetting($user, $currentBusiness);
//        if(null === $bingAccount){
//            $bingAccount = $bingService->createAccount($currentBusiness, $request->get('code'));
//        }
//        if($bingService->getToken($bingAccount->getCode()) === 0){
//            return $this->redirect($bingService->auth());
//        }
        try {
            $bingService->getOwner($bingService->getToken($request->get('code')));
            return $this->redirectToRoute('index');
        }catch (OAuthCompanyException $e){
            return $this->redirectToRoute('index');
        }
    }
}