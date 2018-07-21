<?php

namespace SingAppBundle\Controller;

use FacebookAds\Http\Adapter\Curl\Curl;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
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
        $instagramServices['likes'] = $instagram->newAuth($user, $currentBusiness)->authInst()->getAllLikesCount();
        $instagramServices['comments'] = $instagram->newAuth($user, $currentBusiness)->authInst()->getAllComments();
        $instagramServices['href'] = 'https://anon.to/?https://www.instagram.com/'.$instagram->getIstagramAccount($user, $currentBusiness)->getLogin().'/';
        $params = [
            'businesses' => $this->getBusinesses(),
            'currentBusiness' => $currentBusiness,
            'instagramServices' => $instagramServices
        ];
        return $this->render('@SingApp/interactions/interactions.html.twig', $params);
    }
}
