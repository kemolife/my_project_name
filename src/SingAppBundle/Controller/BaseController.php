<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\BusinessInfoType;
use SingAppBundle\Repository\BusinessInfoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    public function findBy($entity, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $repository = $this->getDoctrine()->getRepository($entity);

        return $repository->findBy($criteria, $orderBy, $limit, $offset);
    }



    public function businessPostForm(BusinessInfo $entity, Request $request, $update = false, User $user)
    {
        if($update){
            $options = ['method' => 'PUT', 'validation_groups' => ['update']];
        }else{
            $options = ['method' => 'POST', 'validation_groups' => ['create']];
        }
        $businessPostForm = $this->createForm(BusinessInfoType::class, $entity, $options);

        $businessPostForm->handleRequest($request);


        if ($businessPostForm->isSubmitted() && $businessPostForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setUser($user);
            $em->persist($entity);
            $em->flush();

        }

        return $businessPostForm;
    }

    public function getCurrentBusiness(Request $request)
    {
        /**
         * @var BusinessInfoRepository $repository
         */
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:BusinessInfo');

        return $repository->getCurrentBusiness($request);
    }

    public function getBusinesses()
    {
        return $this->findBy('SingAppBundle:BusinessInfo', ['user' => $this->getUser()->getId()]);
    }

    public function addBusiness(Request $request, User $user)
    {
        $businessInfo = new Businessinfo();
        $form = $this->createForm('SingAppBundle\Form\BusinessInfoType', $businessInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $businessInfo->setUser($user->getId());
            $em->persist($businessInfo);
            $em->flush();

            return $this->redirectToRoute('index', array('id' => $businessInfo->getId()));
        }
        return $this->render('@SingApp/oauth/add-business.html.twig',  ['form' => $form->createView()]);
    }
}