<?php

namespace ReviewsServicesBundle\Controller;

use ReviewsServicesBundle\Entity\ServicesSetting;
use ReviewsServicesBundle\Entity\SettingData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Servicessetting controller.
 *
 * @Route("services-setting")
 */
class ServicesSettingController extends Controller
{
    /**
     * Creates a new servicesSetting entity.
     *
     * @Route("/new", name="services-setting_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $settingData = new SettingData();
        $form = $this->createForm('ReviewsServicesBundle\Form\ServicesSettingType', $settingData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get('services_setting')->create($settingData);
            } catch (\Exception $e) {
                return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            return $this->redirectToRoute('services-setting_edit');
        }

        return $this->render('@ReviewsServices/servicessetting/new.html.twig', array(
            'servicesSetting' => $settingData,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing servicesSetting entity.
     *
     * @Route("/{user_id}/edit", name="services-setting_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ServicesSetting $servicesSetting)
    {
        $settingData = $this->container->get('services_setting')->deserializeData($servicesSetting);
        $editForm = $this->createForm('ReviewsServicesBundle\Form\ServicesSettingType', $settingData);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {
                $this->container->get('services_setting')->update($settingData, $servicesSetting);
            } catch (\Exception $e) {
                return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }
            return $this->redirectToRoute('services-setting_edit');
        }

        return $this->render('@ReviewsServices/servicessetting/edit.html.twig', array(
            'servicesSetting' => $servicesSetting,
            'edit_form' => $editForm->createView()
        ));
    }
}
