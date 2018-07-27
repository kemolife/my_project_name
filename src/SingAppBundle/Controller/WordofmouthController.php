<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\WordofmouthAccount;
use SingAppBundle\Entity\YelpAccount;
use SingAppBundle\Form\WordofmouthType;
use SingAppBundle\Form\YelpType;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\WordofmouthService;
use SingAppBundle\Services\YelpService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class WordofmouthController extends BaseController
{
    /**
     * @Route("/wordofmouth/auth", name="wordofmouth-auth")
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
        $wordofmouthAccount = new WordofmouthAccount();
        $yelpServices = $this->get('app.wordofmouth.service');
        $form = $this->createForm(WordofmouthType::class, $wordofmouthAccount);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var WordofmouthService $yelpServices
             */
            try{
                $yelpServices->auth($wordofmouthAccount);
                $yelpServices->createAccount($wordofmouthAccount);
                return $this->redirectToRoute('index');
            }catch (OAuthCompanyException $e){
                return $this->render('@SingApp/services-form/wordofmouth.html.twig', ['form' => $form->createView(), 'error' => 'Credential bad or try again later']);
            }
        }

        return $this->render('@SingApp/services-form/wordofmouth.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/worldofmouth-test", name="worldofmouth-test")
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