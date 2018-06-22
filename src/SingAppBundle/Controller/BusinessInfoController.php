<?php

namespace SingAppBundle\Controller;

use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use SingAppBundle\Entity\BusinessInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Businessinfo controller.
 *
 * @Route("business-info")
 */
class BusinessInfoController extends Controller
{
    /**
     * Creates a new businessInfo entity.
     *
     * @Route("/new", name="bussines-info_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $businessInfo = new Businessinfo();
        $form = $this->createForm('SingAppBundle\Form\BusinessInfoType', $businessInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $businessInfo->setUser(1);
            $em->persist($businessInfo);
            $em->flush();

            return $this->redirectToRoute('bussines-info_edit', array('id' => $businessInfo->getId()));
        }

        return $this->render('@SingApp/businessinfo/new.html.twig', array(
            'businessInfo' => $businessInfo,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing businessInfo entity.
     *
     * @Route("/{id}/edit", name="bussines-info_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, BusinessInfo $businessInfo)
    {
        $editForm = $this->createForm('SingAppBundle\Form\BusinessInfoType', $businessInfo);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('bussines-info_edit', array('id' => $businessInfo->getId()));
        }

        return $this->render('@SingApp/businessinfo/edit.html.twig', array(
            'businessInfo' => $businessInfo,
            'edit_form' => $editForm->createView(),
        ));
    }
}
