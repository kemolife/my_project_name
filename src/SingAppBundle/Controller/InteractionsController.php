<?php

namespace SingAppBundle\Controller;

use FacebookAds\Http\Adapter\Curl\Curl;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
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
        return $this->render('@SingApp/interactions/interactions.html.twig');
    }
}
