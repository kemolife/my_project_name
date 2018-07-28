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
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $zomatoAccount = new ZomatoAccount();
        $zomatoServices = $this->get('app.zomato.service');
        $form = $this->createForm(ZomatoType::class, $zomatoAccount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var ZomatoService $zomatoServices
             */
            try{
                $zomatoServices->auth($zomatoAccount);
                $zomatoServices->createAccount($zomatoAccount);
                return $this->redirectToRoute('index');
            }catch (OAuthCompanyException $e){
                return $this->render('@SingApp/services-form/zomato.html.twig', ['form' => $form->createView(), 'error' => 'Credential bad or try again later']);
            }
        }

        return $this->render('@SingApp/services-form/zomato.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/zomato-test", name="zomato-test")
     */
    public function testAction()
    {
        /**
         * @var YelpService $yelpService
         */
        $yelpService = $this->get('app.yelp.service');
        $yelpService->auth();
    }
}