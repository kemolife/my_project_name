<?php


namespace SingAppBundle\Controller;

use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\GooglePost;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\LinkedinPost;
use SingAppBundle\Entity\PinterestAccount;
use SingAppBundle\Entity\PinterestPin;
use SingAppBundle\Entity\User;
use SingAppBundle\Entity\YoutubePost;
use SingAppBundle\Form\InstagramAccountForm;
use SingAppBundle\Form\InstagramPostForm;
use SingAppBundle\Providers\InstagramBusiness;
use SingAppBundle\Services\GoogleService;
use SingAppBundle\Services\InstagramService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SingAppBundle\Services\YoutubeService;
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
        $youtubeVideo = [];
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
        $youtubeAccount = $this->findOneBy('SingAppBundle:YoutubeAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $linkedinAccount = $this->findOneBy('SingAppBundle:LinkedinAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);

        if ($youtubeAccount !== null) {
            /**
             * @var YoutubeService $youtubeService
             */
            $youtubeService = $this->get('app.youtube.service');
            $youtubeVideo = $youtubeService->getUploadVideos($youtubeAccount);
        }

        $params = [
            'businesses' => $this->getBusinesses(),
            'posts' => $posts,
            'instagramAccount' => $instagramAccount,
            'instagramAccountForm' => $instagramAccountForm,
            'instagramPostForm' => $instagramPostForm,
            'googleAccount' => $googleAccount,
            'facebookAccount' => $facebookAccount,
            'pinterestAccount' => $pinterestAccount,
            'currentBusiness' => $currentBusiness,
            'youtubeAccount' => $youtubeAccount,
            'youtubeVideo' => $youtubeVideo,
            'linkedinAccount' => $linkedinAccount,
        ];


        return $this->render('@SingApp/socialNetworkPosts/index.html.twig', $params);
    }

    /**
     * @Route("/social-network-posts/create", name="social-network-posts-create")
     * @Security("has_role('ROLE_USER')")
     */
    public function createAction(Request $request)
    {
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        foreach ($data['socials'] as $social) {
            switch ($social) {
                case 'google':
                    $googlePost = new GooglePost();
                    $googlePost->setTitle($data['title']);
                    $googlePost->setCaption($data['caption']);
                    $googlePost->setPostDate(new \DateTime($data['postDate']));
                    $googlePost->setSocialNetwork('google');
                    $googlePost->setUploadedFiles($request->files->get('media'));
                    $googlePost->setSchedule(intval($request->request->get('schedule')));
                    $googlePost->setBusiness($this->getCurrentBusiness($request));
                    $em->persist($googlePost);
                    break;
                case 'instagram':
                    $instagramPost = new InstagramPost();
                    $instagramPost->setTitle($data['title']);
                    $instagramPost->setCaption($data['caption']);
                    $instagramPost->setPostDate(new \DateTime($data['postDate']));
                    $instagramPost->setSocialNetwork('instagram');
                    $instagramPost->setUploadedFiles($request->files->get('media'));
                    $instagramPost->setSchedule(intval($request->request->get('schedule') === 'on'));
                    $instagramPost->setBusiness($this->getCurrentBusiness($request));

                    $em->persist($instagramPost);
                    break;
                case 'pinterest':
                    if (isset($query['board'])) {
                        $pinterestPin = new PinterestPin();
                        $pinterestPin->setTitle($data['title']);
                        $pinterestPin->setCaption($data['caption']);
                        $pinterestPin->setBoard($data['board']);
                        $pinterestPin->setLink($data['link']);
                        $pinterestPin->setPostDate(new \DateTime($data['postDate']));
                        $pinterestPin->setSocialNetwork('pinterest');
                        $pinterestPin->setUploadedFiles($request->files->get('media'));
                        $pinterestPin->setSchedule(intval($request->request->get('schedule') === 'on'));
                        $pinterestPin->setBusiness($this->getCurrentBusiness($request));

                        $em->persist($pinterestPin);
                    }
                    break;

                case 'youtube':
                    $youtubeVideo = new YoutubePost();
                    $youtubeVideo->setTitle($data['title']);
                    $youtubeVideo->setCaption($data['caption']);
                    $youtubeVideo->setPostDate(new \DateTime($data['postDate']));
                    $youtubeVideo->setSocialNetwork('youtube');
                    $youtubeVideo->setUploadedFiles($request->files->get('media'));
                    $youtubeVideo->setSchedule(intval($request->request->get('schedule') === 'on'));
                    $youtubeVideo->setBusiness($this->getCurrentBusiness($request));

                    $em->persist($youtubeVideo);
                    break;

                case 'linkedin':
                    $linkedinPost = new LinkedinPost();
                    $linkedinPost->setTitle($data['title']);
                    $linkedinPost->setCaption($data['caption']);
                    $linkedinPost->setPostDate(new \DateTime($data['postDate']));
                    $linkedinPost->setSocialNetwork('linkedin');
                    $linkedinPost->setUploadedFiles($request->files->get('media'));
                    $linkedinPost->setSchedule(intval($request->request->get('schedule') === 'on'));
                    $linkedinPost->setBusiness($this->getCurrentBusiness($request));

                    $em->persist($linkedinPost);
                    break;
            }

            $em->flush();
        }

        return $this->redirect($this->generateUrl('social-network-posts', $request->query->all()));
    }
}