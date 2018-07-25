<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Services\PinterestService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Boards controller.
 *
 */
class BoardsController extends BaseController
{
    /**
     * Creates a new businessInfo entity.
     *
     * @Route("/boards", name="boards-list")
     * @Method({"GET"})
     */
    public function newAction(Request $request)
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        try {
            return $this->render('@SingApp/socialNetworkPosts/pinterest/boards/index.html.twig', array(
                'boards' => $pinterestService->getBoards($this->getPinterestAccount($request)),
                'businesses' => $this->getBusinesses(),
            ));
        }catch (\Exception $e){
            return $this->redirect($this->generateUrl('social-network-posts', $request->query->all()+['error' => $e->getMessage()]));
        }
    }

    /**
     *
     * @Route("/board/add", name="board-add")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        if (!empty($request->request->all())){
            try {
                $pinterestService->createBoard($request->request->all(), $this->getPinterestAccount($request));
                return $this->redirectToRoute('boards-list', $request->query->all());
            } catch (\Exception $e) {
                return $this->redirect($this->generateUrl('social-network-posts', $request->query->all() + ['error' => $e->getMessage()]));
            }
        }
        return $this->render('@SingApp/socialNetworkPosts/pinterest/boards/add.html.twig', array(
            'businesses' => $this->getBusinesses()
        ));
    }

    /**
     *
     * @Route("/board/edit/{boardId}", name="board-edit")
     * @Method({"POST"})
     */
    public function editAction(Request $request, $boardId)
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        if (!empty($request->request->all())){
            try {
                $pinterestService->editBoard($boardId, $request->request->all(), $this->getPinterestAccount($request));
                return $this->redirectToRoute('boards-list', $request->query->all());
            } catch (\Exception $e) {
                return $this->redirect($this->generateUrl('social-network-posts', $request->query->all() + ['error' => $e->getMessage()]));
            }
        }
        return $this->render('@SingApp/socialNetworkPosts/pinterest/boards/edit.html.twig', array(
            'businesses' => $this->getBusinesses()
        ));
    }

    /**
     *
     * @Route("/board/delete/{boardId}", name="board-delete")
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, $boardId)
    {
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        try {
            $pinterestService->deleteBoards($boardId, $this->getPinterestAccount($request));
            return $this->redirectToRoute('boards-list', $request->query->all());
        } catch (\Exception $e) {
            return $this->redirect($this->generateUrl('social-network-posts', $request->query->all() + ['error' => $e->getMessage()]));
        }
    }
}
