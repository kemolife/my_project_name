<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\User;
use SingAppBundle\Form\UserRegistryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RegistrationUserController extends BaseController
{
    /**
     * @Route("/register", name="registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserRegistryType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('authorization');
        }

        return $this->render('@SingApp/security/register.html.twig', ['form' => $form->createView()]);
    }
}