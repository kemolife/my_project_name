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
use SingAppBundle\Services\PinterestService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class BaseController extends Controller
{
    protected $session;

    public function __construct()
    {
        $this->session = new Session();
    }

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
        $paymentMethods = [];
        if($update){
            $options = ['method' => 'PUT'];
        }else{
            $options = ['method' => 'POST'];
        }
        $businessPostForm = $this->createForm(BusinessInfoType::class, $entity, $options);

        $businessPostForm->handleRequest($request);


        if ($businessPostForm->isSubmitted() && $businessPostForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setUser($user);
            $entity->setOpeningHours(\GuzzleHttp\json_encode($request->get('singappbundle_businessinfo')['openingHours']));
            if(isset($request->get('singappbundle_businessinfo')['payment_methods'])) {
                $paymentMethods = $request->get('singappbundle_businessinfo')['payment_methods'];
            }
            $entity->setPaymentOptions(\GuzzleHttp\json_encode($paymentMethods));
            $entity->setPhoneNumber($request->get('phone')['receivers_internationl'][0]);
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('index', array('business' => $entity->getId()));

        }

        return $this->render('@SingApp/oauth/add-business.html.twig',  [
            'form' => $businessPostForm->createView(),
            'business' => $entity
            ]);
    }

    public function getCurrentBusiness(Request $request)
    {
        /**
         * @var BusinessInfoRepository $repository
         */
        $repository = $this->getDoctrine()->getRepository('SingAppBundle:BusinessInfo');

        return $repository->getCurrentBusiness($request, $this->getUser());
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
            $businessInfo->setPaymentOptions(\GuzzleHttp\json_encode($request->get('singappbundle_businessinfo')['payment_methods']));
            $businessInfo->setPhoneNumber($request->get('phone')['receivers_internationl'][0]);
            $em->persist($businessInfo);
            $em->flush();

            return $this->redirectToRoute('index', array('business' => $businessInfo->getId()));
        }
        return $this->render('@SingApp/oauth/add-business.html.twig',  ['form' => $form->createView(), 'business' => $this->getCurrentBusiness($request)]);
    }

    public function instagramAccountForm(Request $request)
    {
        $instagramAccount = new InstagramAccount();

        $instagramAccountForm = $this->createForm(InstagramAccountForm::class, $instagramAccount);

        $instagramAccountForm->handleRequest($request);


        if ($instagramAccountForm->isSubmitted() && $instagramAccountForm->isValid() && 'POST' == $request->getMethod()) {
            $em = $this->getDoctrine()->getManager();
            $instagramAccount->setBusiness($this->getCurrentBusiness($request));

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

    public function getPinterestAccount(Request $request)
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var BusinessInfo $business
         */
        $business = $this->getCurrentBusiness($request);
        /**
         * @var PinterestService $pinterestService
         */
        $pinterestService = $this->get('app.pinterest.service');
        return $pinterestService->getPinterestAccount($user, $business);
    }
}