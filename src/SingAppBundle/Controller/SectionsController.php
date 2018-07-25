<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Services\PinterestService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Sections controller.
 *
 */
class SectionsController extends BaseController
{
    /**
     *
     * @Route("/sections/{boardId}", name="sections-list")
     * @Method({"GET"})
     */
    public function newAction(Request $request, $boardId)
    {
        var_dump($boardId);
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        try {
            return $this->render('@SingApp/socialNetworkPosts/pinterest/sections/index.html.twig', array(
                'sections' => $pinterestService->getSectionsByBoard($boardId, $this->getPinterestAccount($request)),
                'businesses' => $this->getBusinesses(),
            ));
        }catch (\Exception $e){
            return $this->redirect($this->generateUrl('social-network-posts', $request->query->all()+['error' => $e->getMessage()]));
        }
    }

    /**
     *
     * @Route("/section/add", name="section-add")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        return $this->render('@SingApp/socialNetworkPosts/pinterest/sections/add.html.twig', array(

        ));
    }

    /**
     *
     * @Route("/section/delete/{sectionName}", name="section-delete")
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, $sectionName)
    {

    }
}
