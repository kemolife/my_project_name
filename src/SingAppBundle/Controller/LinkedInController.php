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
            $pinterestService->createPin($account->getAccessToken()); die;
        }catch (\Exception $e){
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
                $response =  $this->redirect($this->generateUrl('social-network-posts', $request->query->all()).'#pinterest');
            }catch (\Exception $e){
                $response =  $this->redirect($this->generateUrl('social-network-posts', $request->query->all()+['error' => $e->getMessage()]).'#pinterest');
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
        try {
            $this->get('app.pinterest.service')->deletePin($pinterestPin->getMediaId(), $this->getPinterestAccount($request));
            $em->remove($pinterestPin);
            $em->flush();
            $response = $this->redirectToRoute('social-network-posts', $request->query->all().'#pinterest');
        } catch (\Exception $e) {
            $response = $this->redirectToRoute('social-network-posts', ['error' => $e->getMessage()] + $request->query->all().'#pinterest');
        }
        return $response;
    }
}