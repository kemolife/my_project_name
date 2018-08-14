<?php

namespace SingAppBundle\Controller;


use JMS\JobQueueBundle\Entity\Job;
use ReflectionClass;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\FacebookPost;
use SingAppBundle\Entity\GooglePost;
use SingAppBundle\Entity\HotfrogAccount;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\LinkedinPost;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Entity\Post;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\YoutubeAccount;
use SingAppBundle\Entity\YoutubePost;
use SingAppBundle\Entity\ZomatoAccount;
use SingAppBundle\Form\BusinessInfoType;
use SingAppBundle\Form\FacebookPostForm;
use SingAppBundle\Form\GooglePostForm;
use SingAppBundle\Form\InstagramAccountForm;
use SingAppBundle\Form\InstagramPostForm;
use SingAppBundle\Form\LinkedinPostForm;
use SingAppBundle\Form\PinterestPostForm;
use SingAppBundle\Form\PostForm;
use SingAppBundle\Form\YoutubePostForm;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Repository\BusinessInfoRepository;
use SingAppBundle\Services\interfaces\BaseInterface;
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
        if ($update) {
            $options = ['method' => 'PUT'];
        } else {
            $options = ['method' => 'POST'];
        }
        $businessPostForm = $this->createForm(BusinessInfoType::class, $entity, $options);

        $businessPostForm->handleRequest($request);


        if ($businessPostForm->isSubmitted() && $businessPostForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setUser($user);
            $entity->setOpeningHours(\GuzzleHttp\json_encode($request->get('singappbundle_businessinfo')['openingHours']));
            if (isset($request->get('singappbundle_businessinfo')['payment_methods'])) {
                $paymentMethods = $request->get('singappbundle_businessinfo')['payment_methods'];
            }
            $entity->setPaymentOptions(\GuzzleHttp\json_encode($paymentMethods));
            $entity->setPhoneNumber($request->get('phone')['receivers_internationl'][0]);
            $em->persist($entity);
            $em->flush();
            try {
                $this->updateConnectServices($request);
            } catch (OAuthCompanyException $e) {
                return $this->redirectToRoute('index', ['business' => $entity->getId(), 'error' => $e->getMessage()]);
            }

            return $this->redirectToRoute('index', array('business' => $entity->getId()));

        }

        return $this->render('@SingApp/oauth/add-business.html.twig', [
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
        $paymentMethods = [];
        $businessInfo = new Businessinfo();
        $form = $this->createForm(BusinessInfoType::class, $businessInfo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $businessInfo->setUser($user);
            $businessInfo->setOpeningHours(\GuzzleHttp\json_encode($request->get('singappbundle_businessinfo')['openingHours']));
            if (isset($request->get('singappbundle_businessinfo')['payment_methods'])) {
                $paymentMethods = $request->get('singappbundle_businessinfo')['payment_methods'];
            }
            $businessInfo->setPaymentOptions(\GuzzleHttp\json_encode($paymentMethods));
            $businessInfo->setPhoneNumber($request->get('phone')['receivers_internationl'][0]);
            $em->persist($businessInfo);
            $em->flush();

            return $this->redirectToRoute('index', array('business' => $businessInfo->getId()));
        }
        return $this->render('@SingApp/oauth/add-business.html.twig', ['form' => $form->createView(), 'business' => $this->getCurrentBusiness($request)]);
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

    public function linkedinPostForm(Request $request)
    {
        $linkedinPost = new LinkedinPost();

        $linkedinPostForm = $this->createForm(LinkedinPostForm::class, $linkedinPost);

        $linkedinPostForm->handleRequest($request);


        if ($linkedinPostForm->isSubmitted() && $linkedinPostForm->isValid() && 'POST' == $request->getMethod()) {
            $this->setSavePostData($linkedinPost, $request, 'linkedin');
        }

        return $linkedinPostForm;
    }

    public function youtubePostForm(Request $request, $channels)
    {
        $youtubePost = new YoutubePost();

        $youtubePostForm = $this->createForm(YoutubePostForm::class, $youtubePost, ['channels' => $channels]);

        $youtubePostForm->handleRequest($request);


        if ($youtubePostForm->isSubmitted() && $youtubePostForm->isValid() && 'POST' == $request->getMethod()) {
            $this->setSavePostData($youtubePost, $request, 'youtube');
        }

        return $youtubePostForm;
    }

    public function pinterestPostForm(Request $request, $boards)
    {
        $youtubePost = new PinterestPin();

        $youtubePostForm = $this->createForm(PinterestPostForm::class, $youtubePost, ['boards' => $boards]);

        $youtubePostForm->handleRequest($request);


        if ($youtubePostForm->isSubmitted() && $youtubePostForm->isValid() && 'POST' == $request->getMethod()) {
            $this->setSavePostData($youtubePost, $request, 'pinterest');
        }

        return $youtubePostForm;
    }


    public function googlePostForm(Request $request)
    {
        $youtubePost = new GooglePost();

        $youtubePostForm = $this->createForm(GooglePostForm::class, $youtubePost);

        $youtubePostForm->handleRequest($request);


        if ($youtubePostForm->isSubmitted() && $youtubePostForm->isValid() && 'POST' == $request->getMethod()) {
            $this->setSavePostData($youtubePost, $request, 'google');
        }

        return $youtubePostForm;
    }

    public function facebookPostForm(Request $request)
    {
        $youtubePost = new FacebookPost();

        $youtubePostForm = $this->createForm(FacebookPostForm::class, $youtubePost);

        $youtubePostForm->handleRequest($request);


        if ($youtubePostForm->isSubmitted() && $youtubePostForm->isValid() && 'POST' == $request->getMethod()) {
            $this->setSavePostData($youtubePost, $request, 'facebook');
        }

        return $youtubePostForm;
    }

    public function instagramPostForm(Request $request)
    {
        $youtubePost = new InstagramPost();

        $youtubePostForm = $this->createForm(InstagramPostForm::class, $youtubePost);

        $youtubePostForm->handleRequest($request);


        if ($youtubePostForm->isSubmitted() && $youtubePostForm->isValid() && 'POST' == $request->getMethod()) {
            $this->setSavePostData($youtubePost, $request, 'instagram');
        }

        return $youtubePostForm;
    }

    private function setSavePostData(Post $post, Request $request, $type)
    {
        $em = $this->getDoctrine()->getManager();

        $post->setPostDate(new \DateTime($request->request->get('postDate')));
        $post->setSocialNetwork($type);
        $post->setUploadedFiles($request->files->get('media'));
        $post->setSchedule(intval($request->request->get('schedule') === 'on'));
        $post->setBusiness($this->getCurrentBusiness($request));
        $em->persist($post);
        $em->flush();
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

    public function updateConnectServices(Request $request)
    {
        $servicesAccount = $this->getSwitchServices($request);
        $em = $this->getDoctrine()->getManager();
        foreach ($servicesAccount as $serviceAccount) {
            if ($serviceAccount instanceof BaseInterface) {
                $job = new Job('app:update:service', [$serviceAccount->getId()]);
                $em->persist($job);
                $em->flush();
            }
        }
    }
}