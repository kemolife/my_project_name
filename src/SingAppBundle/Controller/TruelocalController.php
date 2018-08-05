<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\TruelocalAccount;
use SingAppBundle\Form\TruelocalType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\TruelocalService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class TruelocalController extends BaseController
{
    /**
     * @Route("/truelocal/auth", name="truelocal-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $this->session->set('business', $request->get('business'));
        $truelocalAccount = new truelocalAccount();
        $truelocalServices = $this->get('app.truelocal.service');
        $form = $this->createForm(TruelocalType::class, $truelocalAccount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var TruelocalService $truelocalServices
             */
            try {
                $url = $truelocalServices->auth($truelocalAccount);
                $profile = $truelocalServices->getProfileData($url, $truelocalAccount);
                $truelocalAccount = $truelocalServices->createAccount($truelocalAccount, $profile);
                $truelocalServices->editAccount($truelocalAccount, $this->getCurrentBusiness($request));
                return $this->redirectToRoute('index', $request->query->all());
            } catch (OAuthCompanyException $e) {
                return $this->render('@SingApp/services-form/truelocal.html.twig', ['form' => $form->createView(), 'error' => $e->getMessage()]);
            }
        }

        return $this->render('@SingApp/services-form/truelocal.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/truelocal/create", name="truelocal-create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var TruelocalService $truelocalServices
         */
        $truelocalServices = $this->get('app.truelocal.service');
        $elementsCaptcha = $truelocalServices->getCaptcha();
        if($request->getMethod() === 'POST') {
            try {
                $truelocalServices->createServiceAccount($request->request->all(), $currentBusiness);
                return $this->redirectToRoute('truelocal-auth', $request->query->all());
            } catch (OAuthCompanyException $e) {
                return $this->render('@SingApp/new-service-account/truelocal.html.twig', ['elementsCaptcha' => $elementsCaptcha, 'error' => $e->getMessage()]);
            }
        }
        return $this->render('@SingApp/new-service-account/truelocal.html.twig', ['elementsCaptcha' => $elementsCaptcha]);
    }

    /**
     * @Route("/truelocal-test", name="truelocal-test")
     * @param Request $request
     * @throws OAuthCompanyException
     */
    public function testAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var TruelocalService $truelocalServices
         */
        $truelocalServices = $this->get('app.truelocal.service');
        $account = $truelocalServices->getAccount($user, $currentBusiness);
        $truelocalServices->editAccount($account, $currentBusiness);
    }
}