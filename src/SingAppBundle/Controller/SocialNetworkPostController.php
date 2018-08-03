<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\GooglePost;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Entity\User;
use SingAppBundle\Form\InstagramAccountForm;
use SingAppBundle\Form\InstagramPostForm;
use SingAppBundle\Providers\InstagramBusiness;
use SingAppBundle\Services\GoogleService;
use SingAppBundle\Services\InstagramService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class SocialNetworkPostController extends BaseController
{
    /**
     * @Route("/social-network-posts", name="social-network-posts")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {

        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        $instagramAccountForm = $this->instagramAccountForm($request)->createView();
        $instagramPostForm = $this->instagramPostForm($request)->createView();

        $user = $this->getUser();

        $posts = $this->findBy('SingAppBundle:Post', ['user' => $user->getId()], ['postDate' => 'DESC']);
        $instagramAccount = $this->findOneBy('SingAppBundle:InstagramAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $googleAccount = $this->findOneBy('SingAppBundle:GoogleAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $facebookAccount = $this->findOneBy('SingAppBundle:FacebookAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $pinterestAccount = $this->findOneBy('SingAppBundle:PinterestAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $activeServices = $this->getSwitchServices($request);

        $params = [
            'businesses' => $this->getBusinesses(),
            'posts' => $posts,
            'instagramAccount' => $instagramAccount,
            'instagramAccountForm' => $instagramAccountForm,
            'instagramPostForm' => $instagramPostForm,
            'googleAccount' => $googleAccount,
            'facebookAccount' => $facebookAccount,
            'pinterestAccount' => $pinterestAccount,
            'activeServices' => $activeServices,
            'currentBusiness' => $currentBusiness
        ];


        return $this->render('@SingApp/socialNetworkPosts/index.html.twig', $params);
    }

    /**
     * @Route("/social-network-posts/create", name="social-network-posts-create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $dataSocial = $this->getSwitchServices($request);
        $query = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        foreach ($dataSocial as $social) {
            switch ($social) {
                case $social instanceof GoogleAccount :
                    $googlePost = new GooglePost();
                    $googlePost->setTitle($query['title']);
                    $googlePost->setCaption($query['caption']);
                    $googlePost->setPostDate(new \DateTime($query['postDate']));
                    $googlePost->setSocialNetwork('google');
                    $googlePost->setUploadedFiles($request->files->get('photos'));
                    $googlePost->setSchedule(intval($request->request->get('schedule')));
                    $googlePost->setBusiness($this->getCurrentBusiness($request));
                    $em->persist($googlePost);
                    break;
                case $social instanceof InstagramAccount:
                    $instagramPost = new InstagramPost();
                    $instagramPost->setTitle($query['title']);
                    $instagramPost->setCaption($query['caption']);
                    $instagramPost->setPostDate(new \DateTime($query['postDate']));
                    $instagramPost->setSocialNetwork('instagram');
                    $instagramPost->setUploadedFiles($request->files->get('media'));
                    $instagramPost->setSchedule(intval($request->request->get('schedule') === 'on'));
                    $instagramPost->setBusiness($this->getCurrentBusiness($request));

                    $em->persist($instagramPost);
                    break;
                case $social instanceof PinterestAccount:
                    if(isset($query['board'])) {
                        $pinterestPin = new PinterestPin();
                        $pinterestPin->setTitle($query['title']);
                        $pinterestPin->setCaption($query['caption']);
                        $pinterestPin->setBoard($query['board']);
                        $pinterestPin->setLink($query['link']);
                        $pinterestPin->setPostDate(new \DateTime($query['postDate']));
                        $pinterestPin->setSocialNetwork('instagram');
                        $pinterestPin->setUploadedFiles($request->files->get('photos'));
                        $pinterestPin->setSchedule(intval($request->request->get('schedule') === 'on'));
                        $pinterestPin->setBusiness($this->getCurrentBusiness($request));

                        $em->persist($pinterestPin);
                    }
                    break;
            }

            $em->flush();
        }

        return $this->redirect($this->generateUrl('social-network-posts', $request->query->all()));
    }
}