<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\PinPostForm;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\PinterestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PinterestController extends BaseController
{
    const SERVICE_NAME = 'pinterest';
    const SERVICE_MASSAGE = '';

    /**
     * @Route("/auth/pinterest", name="pinterest-auth")
     */
    public function authAction(Request $request)
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
        return $this->redirect($pinterestService->auth());
    }

    /**
     * @Route("/pinterest/oauth2callback", name="pinterest-oauth2callback")
     */
    public function pinterestCallbackAction(Request $request)
    {
        /**
         * @var PinterestService $pinterestService
         */
        try {
            $pinterestService = $this->get('app.pinterest.service');
            $accessTokeData = $pinterestService->getToken($request->get('code'));
            $pinterestService->createAccount($accessTokeData);
            return $this->redirectToRoute($this->session->get('url'), ['business' => $this->session->get('business')]);
        } catch (OAuthCompanyException $e) {
            return $this->redirectToRoute($this->session->get('url'), ['error' => $e->getMessage(), 'business' => $this->session->get('business')]);
        }
    }

    /**
     * @Route("/pinterest-test", name="pinterest-test")
     */
    public function testAction(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var BusinessInfo $business
         */
        $business = $this->getCurrentBusiness($request);
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        $account = $pinterestService->getPinterestAccount($user, $business);
        try {
            $pinterestService->createPin($account->getAccessToken());
            die;
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /**
     * @Route("/pinterest/pin-edit/{pinterestPin}", name="pin-edit")
     */
    public function editPinAction(PinterestPin $pinterestPin, Request $request)
    {

        $pinForm = $this->createForm(PinPostForm::class, $pinterestPin);

        $pinForm->handleRequest($request);


        if ($pinForm->isSubmitted() && $pinForm->isValid() && 'POST' == $request->getMethod()) {
            try {
                $this->get('app.pinterest.service')->editPin($pinterestPin);
                $em = $this->getDoctrine()->getManager();

                $em->persist($pinterestPin);
                $em->flush();
                $response = $this->redirect($this->generateUrl('social-network-posts', $request->query->all()) . '#pinterest');
            } catch (\Exception $e) {
                $response = $this->redirect($this->generateUrl('social-network-posts', $request->query->all() + ['error' => $e->getMessage()]) . '#pinterest');
            }

        } else {
            $response = $this->render('@SingApp/socialNetworkPosts/pinterest/edit.html.twig', [
                'form' => $pinForm->createView(),
                'businesses' => $this->getBusinesses(),
            ]);
        }

        return $response;
    }

    /**
     * @Route("/pinterest/pin-delete/{pinterestPin}", name="pin-delete")
     */
    public function deletePinAction(PinterestPin $pinterestPin, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($pinterestPin);
        $em->flush();
    }

    /**
     * @Route("/pinterest/post", name="pinterest-post")
     */
    public function postAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        $user = $this->getUser();

        /** @var PinterestService $pinterestService */
        $pinterestService = $this->get('app.pinterest.service');

        $pinterestAccount = $this->findOneBy('SingAppBundle:PinterestAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $boards = $pinterestService->getBoards($pinterestAccount);
        $pinterestForm = $this->pinterestPostForm($request, $boards)->createView();
        $pinterestPosts = $posts = $this->findBy('SingAppBundle:Post', ['user' => $user->getId(), 'business' => $currentBusiness->getId(), 'socialNetwork' => self::SERVICE_NAME], ['postDate' => 'DESC']);

        $params = [
            'businesses' => $this->getBusinesses(),
            'form' => $pinterestForm,
            'posts' => $pinterestPosts,
            'account' => $pinterestAccount,
            'service' => self::SERVICE_NAME,
            'massage' => self::SERVICE_MASSAGE,
            'currentBusiness' => $currentBusiness,
            'canDelete' => true
        ];

        return $this->render('@SingApp/socialNetworkPosts/index.html.twig', $params);
    }
}