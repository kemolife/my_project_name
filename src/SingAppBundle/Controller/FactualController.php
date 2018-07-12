<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\YelpAccount;
use SingAppBundle\Form\YelpType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\FactualService;
use SingAppBundle\Services\YelpService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class FactualController extends BaseController
{
    /**
     * @Route("/factual/auth", name="factual-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $factualService = $this->get('app.factual.service');
            try{
//                $factualServices->createAccount($currentBusiness, $yelp);
                $factualService->auth();
                return $this->redirectToRoute('index');
            }catch (OAuthCompanyException $e){
//                return $this->render('@SingApp/services-form/yelp.html.twig', ['form' => $form->createView(), 'error' => 'Credential bad or try again later']);
            }
//        return $this->render('@SingApp/services-form/yelp.html.twig', ['form' => $form->createView()]);
    }
}