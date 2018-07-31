<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\HotfrogAccount;
use SingAppBundle\Form\HotfrogType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\HotfrogService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class HotfrogController extends BaseController
{
    /**
     * @Route("/hotfrog/auth", name="hotfrog-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $this->session->set('business', $request->get('business'));
        $hotfrogAccount = new HotfrogAccount();
        $hotfrogServices = $this->get('app.hotfrog.service');
        $form = $this->createForm(HotfrogType::class, $hotfrogAccount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var HotfrogService $hotfrogServices
             */
            try {
                $companyId = $hotfrogServices->auth($hotfrogAccount);
                $hotfrogAccount = $hotfrogServices->createAccount($hotfrogAccount, $companyId);
                $hotfrogServices->editAccount($hotfrogAccount, $this->getCurrentBusiness($request));
                return $this->redirectToRoute('index', $request->query->all());
            } catch (OAuthCompanyException $e) {
                return $this->render('@SingApp/services-form/hotfrog.html.twig', ['form' => $form->createView(), 'error' => $e->getMessage()]);
            }
        }

        return $this->render('@SingApp/services-form/hotfrog.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/hotfrog/create", name="hotfrog-create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var HotfrogService $hotfrogServices
         */
        $hotfrogServices = $this->get('app.hotfrog.service');
        $elementsCaptcha = $hotfrogServices->getCaptcha();
        if($request->getMethod() === 'POST') {
            try {
                $hotfrogServices->createServiceAccount($request->request->all(), $currentBusiness);
                return $this->redirectToRoute('hotfrog-auth', $request->query->all());
            } catch (OAuthCompanyException $e) {
                return $this->render('@SingApp/new-service-account/hotfrog.html.twig', ['elementsCaptcha' => $elementsCaptcha, 'error' => $e->getMessage()]);
            }
        }
        return $this->render('@SingApp/new-service-account/hotfrog.html.twig', ['elementsCaptcha' => $elementsCaptcha]);
    }

    /**
     * @Route("/hotfrog-test", name="hotfrog-test")
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
         * @var HotfrogService $hotfrogServices
         */
        $hotfrogServices = $this->get('app.hotfrog.service');
        $account = $hotfrogServices->getAccount($user, $currentBusiness);
        $hotfrogServices->editAccount($account, $currentBusiness);
    }
}