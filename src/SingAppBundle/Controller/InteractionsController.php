<?php

namespace SingAppBundle\Controller;

use FacebookAds\Http\Adapter\Curl\Curl;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Providers\InstagramBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Businessinfo controller.
 *
 */
class InteractionsController extends BaseController
{
    /**
     * @Route("/interactions", name="interactions")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        $instagramServices = [];
        $user = $this->getUser();

        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var InstagramBusiness $instagram
         */
        $instagram = $this->get('instagram_provider');
        $googleService = $this->get('app.google.service');
        $googleReviews = [];
        $googleAccount = $this->findOneBy('SingAppBundle:GoogleAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        if ($googleAccount instanceof GoogleAccount && $googleAccount->getLocation()) {
            $googleReviews = $googleService->getReviews($googleAccount);
        }
        if (null !== $instagram->getIstagramAccount($user, $currentBusiness)) {
            $instagramServices['likes'] = $instagram->newAuth($user, $currentBusiness)->authInst()->getAllLikesCount();
            $instagramServices['comments'] = $instagram->newAuth($user, $currentBusiness)->authInst()->getAllComments();
            $instagramServices['href'] = 'https://anon.to/?https://www.instagram.com/' . $instagram->getIstagramAccount($user, $currentBusiness)->getLogin() . '/';
            $params = [
                'businesses' => $this->getBusinesses(),
                'currentBusiness' => $currentBusiness,
                'instagramServices' => $instagramServices,
                'googleReviews' => $googleReviews,
            ];
        }else{
            return $this->redirectToRoute('index', $request->query->all()+['error' => 'Please connect to instagram service!']);
        }
        return $this->render('@SingApp/interactions/interactions.html.twig', $params);
    }

    /**
     * @Route("/interactions-comments/add", name="interactions-comment-add")
     * @Security("has_role('ROLE_USER')")
     */
    public function commentAddAction(Request $request)
    {
        $dataPost = $request->request->all();
        $dataGet = $request->query->all();

        $user = $this->getUser();
        $currentBusiness = $this->getCurrentBusiness($request);
        /**
         * @var InstagramBusiness $instagramService
         */
        $instagramService = $this->get('instagram_provider');
        try{
            $instagramService->auth($user, $currentBusiness)->addComment($dataGet['mediaId'], $dataGet['comment'], $dataPost['text']);
            return $this->redirectToRoute('interactions', ['business' => $request->query->all()['business']]);
        }catch (OAuthCompanyException $e){
            return $this->redirectToRoute('interactions', ['business' => $request->query->all()['business'], 'error' => $e->getMessage()]);
        }
    }
}
