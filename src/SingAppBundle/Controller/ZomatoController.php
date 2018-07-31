<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\ZomatoAccount;
use SingAppBundle\Form\ZomatoType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\ZomatoService;
use SingAppBundle\Services\YelpService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ZomatoController extends BaseController
{
    /**
     * @Route("/zomato/auth", name="zomato-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $this->session->set('business', $request->get('business'));
        $zomatoAccount = new ZomatoAccount();
        $zomatoServices = $this->get('app.zomato.service');
        $form = $this->createForm(ZomatoType::class, $zomatoAccount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var ZomatoService $zomatoServices
             */
            try{
                $serviceUserId = $zomatoServices->auth($zomatoAccount);
                $zomatoAccount = $zomatoServices->createAccount($zomatoAccount, $serviceUserId);
                $zomatoServices->editAccount($zomatoAccount, $this->getCurrentBusiness($request));
                return $this->redirectToRoute('index', $request->query->all());
            }catch (OAuthCompanyException $e){
                return $this->render('@SingApp/services-form/zomato.html.twig', ['form' => $form->createView(), 'error' => $e->getMessage()]);
            }
        }

        return $this->render('@SingApp/services-form/zomato.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/zomato-test", name="zomato-test")
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
         * @var ZomatoService $zomatoServices
         */
        $zomatoServices = $this->get('app.zomato.service');
        $account = $zomatoServices->getAccount($user, $currentBusiness);
        $zomatoServices->editAccount($account);
    }
}