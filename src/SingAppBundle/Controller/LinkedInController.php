<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\PinPostForm;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\LinkedInService;
use SingAppBundle\Services\PinterestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class LinkedInController extends BaseController
{
    const SERVICE_NAME = 'linkedin';
    const SERVICE_MASSAGE = 'LinkedIn service download only one image and the image should be at least 80 x 150px for best results.';
    /**
     * @Route("/auth/linkedin", name="linkedin-auth")
     */
    public function authAction(Request $request)
    {
        /**
         * @var LinkedInService $linkedInService
         */
        $linkedInService = $this->get('app.linkedIn.service');
        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
        return $this->redirect($linkedInService->auth());
    }

    /**
     * @Route("/linkedin/oauth2callback", name="linkedin-oauth2callback")
     */
    public function pinterestCallbackAction(Request $request)
    {
        /**
         * @var LinkedInService $linkedInService
         */
        try {
            $linkedInService = $this->get('app.linkedIn.service');
            $accessTokeData = $linkedInService->getToken($request->get('code'));
            $linkedInService->createAccount($accessTokeData);
            return $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
        }catch (OAuthCompanyException $e){
            return $this->redirectToRoute($this->session->get('url'), ['error' => $e->getMessage(), 'business' => $this->session->get('business')]);
        }
    }

    /**
     * @Route("/linkedin/post", name="linkedin-post")
     */
    public function postAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        $user = $this->getUser();
        $linkedinForm = $this->linkedinPostForm($request)->createView();
        $linkedinPosts = $posts = $this->findBy('SingAppBundle:Post', ['user' => $user->getId(), 'business' => $currentBusiness->getId(), 'socialNetwork' => self::SERVICE_NAME], ['postDate' => 'DESC']);
        $linkedinAccount = $this->findOneBy('SingAppBundle:LinkedinAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);

        $params = [
            'businesses' => $this->getBusinesses(),
            'form' => $linkedinForm,
            'posts' => $linkedinPosts,
            'account' => $linkedinAccount,
            'service' => self::SERVICE_NAME,
            'massage' => self::SERVICE_MASSAGE,
            'currentBusiness' => $currentBusiness,
            'canDelete' => false
        ];

        return $this->render('@SingApp/socialNetworkPosts/index.html.twig', $params);
    }
}