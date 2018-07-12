<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\YelpAccount;
use SingAppBundle\Form\YelpType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\YelpService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class YelpController extends BaseController
{
    /**
     * @Route("/yelp/auth", name="yelp-auth")
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
        $yelp = new YelpAccount();
        $yelpServices = $this->get('app.yelp.service');
        $form = $this->createForm(YelpType::class, $yelp);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var YelpService $yelpServices
             */
            try{
                $yelpServices->createAccount($currentBusiness, $yelp);
                $yelpServices->auth($user, $currentBusiness);
                return $this->redirectToRoute('index');
            }catch (OAuthCompanyException $e){
                return $this->render('@SingApp/services-form/yelp.html.twig', ['form' => $form->createView(), 'error' => 'Credential bad or try again later']);
            }
        }

        return $this->render('@SingApp/services-form/yelp.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/yelp-test", name="yelp-test")
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