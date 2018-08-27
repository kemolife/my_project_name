<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\Service;
use SingAppBundle\Entity\User;
use SingAppBundle\Services\ScanService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Businessinfo controller.
 */
class ScanController extends BaseController
{
    /**
     * Creates a new businessInfo entity.
     *
     * @Route("/scan", name="scan")
     * @Security("has_role('ROLE_USER')")
     */
    public function showServicesList(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        if(empty($this->getBusinesses())){
            return $this->addBusiness($request, $user);
        }

        /**
         * Get service list from DB
         */
        $repository = $this->getRepository('SingAppBundle:Service');

        /**
         * @var Service $service
         */
        $service = $repository->findAll();
        
        $currentBusiness = $this->getCurrentBusiness($request);
        $params = [
            'businesses' => $this->getBusinesses(),
            'currentBusiness' => $currentBusiness
        ];
        return $this->render('@SingApp/scan/index-scan.html.twig', $params);
    }
    
}
