<?php

namespace SingAppBundle\Controller;


use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\BusinessInfoType;
use SingAppBundle\Form\InstagramAccountForm;
use SingAppBundle\Form\InstagramPostForm;
use SingAppBundle\Repository\BusinessInfoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    public function getRepository($entity)
    {
        return $this->getDoctrine()->getRepository($entity);
    }

    public function findBy($entity, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $repository = $this->getDoctrine()->getRepository($entity);

        return $repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy($entity, array $criteria)
    {
        $repository = $this->getDoctrine()->getRepository($entity);

        return $repository->findOneBy($criteria);
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
        $form = $this->createForm(BusinessInfoType::class, $businessInfo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $businessInfo->setUser($user);
            $businessInfo->setOpeningHours(\GuzzleHttp\json_encode($request->get('singappbundle_businessinfo')['openingHours']));
            $em->persist($businessInfo);
            $em->flush();

            return $this->redirectToRoute('index', array('business' => $businessInfo->getId()));
        }
        return $this->render('@SingApp/oauth/add-business.html.twig',  ['form' => $form->createView()]);
    }

    public function instagramAccountForm(Request $request)
    {
        $instagramAccount = new InstagramAccount();

        $instagramAccountForm = $this->createForm(InstagramAccountForm::class, $instagramAccount);

        $instagramAccountForm->handleRequest($request);


        if ($instagramAccountForm->isSubmitted() && $instagramAccountForm->isValid() && 'POST' == $request->getMethod()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($instagramAccount);
            $em->flush();

        }

        return $instagramAccountForm;
    }

    public function instagramPostForm(Request $request)
    {
        $instagramPost = new InstagramPost();

        $instagramPostForm = $this->createForm(InstagramPostForm::class, $instagramPost);

        $instagramPostForm->handleRequest($request);


        if ($instagramPostForm->isSubmitted() && $instagramPostForm->isValid() && 'POST' == $request->getMethod()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($instagramPost);
            $em->flush();

        }

        return $instagramPostForm;
    }

    public function getSwitchServices(Request $request)
    {
        return $this->findBy('SingAppBundle:SocialNetworkAccount', ['user' => $this->getUser()->getId(), 'business' => $this->getCurrentBusiness($request)->getId()]);
    }
}