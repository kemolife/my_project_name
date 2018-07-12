<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Form\SignInForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthorizationController extends Controller
{
    /**
     * @Route("/authorization", name="authorization")
     */
    public function indexAction(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $form = $this->createForm(SignInForm::class);
        if ($error) {
            $formError = new FormError($error->getMessage());
            $form->addError($formError);
        }

        return $this->render('@SingApp/security/sign-in.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/logout", name="logout")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logoutActiot(Request $request)
    {
        return $this->redirectToRoute('authorization');
    }
}
