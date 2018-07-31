<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\InstagramAccountForm;
use SingAppBundle\Form\InstagramPostForm;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Providers\InstagramBusiness;
use SingAppBundle\Services\InstagramService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class InstagramController extends BaseController
{
    /**
     * @Route("/instagram", name="instagram-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $this->session->set('business', $request->get('business'));
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var InstagramBusiness $instagramService
         */
        $instagramService = $this->get('instagram_provider');

        $instagram = new InstagramAccount();
        if(null !== $instagramService->getIstagramAccount($user, $currentBusiness)) {
            $instagram = $instagramService->getIstagramAccount($user, $currentBusiness);
        }
        $form = $this->createForm(InstagramAccountForm::class, $instagram);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var InstagramBusiness $instagramService
             */
            try{
                $instagramService->auth($user, $currentBusiness, $instagram)->updateIstagramAccount();
                $instagramService->createUpdateAccount($currentBusiness, $instagram);
                return $this->redirectToRoute('index', $request->query->all());
            }catch (OAuthCompanyException $e){
                return $this->render('@SingApp/services-form/instagram.html.twig', ['form' => $form->createView(), 'error' => $e->getMessage()]);
            }
        }

        return $this->render('@SingApp/services-form/instagram.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/instagram/comments/{instagramPost}", name="instagram-comments")
     */
    public function commentsAction(InstagramPost $instagramPost, Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        $user = $this->getUser();

        /**
         * @var InstagramService $instagramService
         */
        $instagramService = $this->get('app.instagram.service');

        return $this->render('@SingApp/socialNetworkPosts/instagram/comments.html.twig', [
            'comments' => $instagramService->getComments($instagramPost),
            'businesses' => $this->getBusinesses(),
        ]);
    }

    /**
     * @Route("/instagram/post/{instagramPost}", name="instagram-post-edit")
     */
    public function editPostAction(InstagramPost $instagramPost, Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);


        $user = $this->getUser();

        $instagramPostForm = $this->createForm(InstagramPostForm::class, $instagramPost);

        $instagramPostForm->handleRequest($request);


        if ($instagramPostForm->isSubmitted() && $instagramPostForm->isValid() && 'POST' == $request->getMethod()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($instagramPost);
            $em->flush();

            $response = $this->redirectToRoute('social-network-posts', $request->query->all());

        } else {
            $response = $this->render('@SingApp/socialNetworkPosts/instagram/edit.html.twig', [
                'form' => $instagramPostForm->createView(),
                'businesses' => $this->getBusinesses(),
            ]);
        }

        return $response;
    }

    /**
     * @Route("/instagram/account/{instagramAccount}", name="instagram-account-edit")
     */
    public function editAccountAction(InstagramAccount $instagramAccount, Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        $user = $this->getUser();

        $instagramAccountForm = $this->createForm(InstagramAccountForm::class, $instagramAccount);

        $instagramAccountForm->handleRequest($request);


        if ($instagramAccountForm->isSubmitted() && $instagramAccountForm->isValid() && 'POST' == $request->getMethod()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($instagramAccount);
            $em->flush();

            $response = $this->redirectToRoute('social-network-posts', $request->query->all());

        } else {
            $response = $this->render('@SingApp/socialNetworkPosts/instagram/edit.html.twig', [
                'form' => $instagramAccountForm->createView(),
                'businesses' => $this->getBusinesses(),
            ]);
        }

        return $response;
    }

    /**
     * @Route("/instagram/account-delete/{instagramAccount}", name="instagram-account-delete")
     * @Security("is_granted('ABILITY_INSTAGRAM_ACCOUNT_DELETE', instagramAccount)")
     */
    public function deleteAccountAction(InstagramAccount $instagramAccount, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($instagramAccount);
        $em->flush();

        return $this->redirect($this->generateUrl('social-network-posts', $request->query->all()).'#instagram');
    }

    /**
     * @Route("/instagram/post-delete/{instagramPost}", name="instagram-post-delete")
     */
    public function deletePostAction(InstagramPost $instagramPost, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $this->get('app.instagram.service')->deletePost($instagramPost);
            $em->remove($instagramPost);
            $em->flush();
            $response = $this->redirectToRoute('social-network-posts', $request->query->all());
        }catch (OAuthCompanyException $e){
            $response = $this->redirectToRoute('social-network-posts', $request->query->all() + ['error' => $e->getMessage()]);
        }catch (\Doctrine\DBAL\DBALException $e){
            $response = $this->redirectToRoute('social-network-posts', $request->query->all() + ['error' => $e->getMessage()]);
        }
        return $response;
    }
}