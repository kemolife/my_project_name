<?php


namespace SingAppBundle\Controller;

use Google_Exception;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\GoogleAccount;
use SingAppBundle\Entity\Post;
use SingAppBundle\Entity\YoutubeAccount;
use SingAppBundle\Entity\YoutubePost;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\GoogleService;
use SingAppBundle\Services\YoutubeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class YoutubeController extends BaseController
{
    const SERVICE_NAME = 'youtube';
    const SERVICE_MASSAGE = 'Youtube service download only video, your images save like a map of thumbnail images associated with the video';

    /**
     * @Route("/youtube/auth", name="youtube-auth")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var YoutubeService $youtubeService
         */
        $youtubeService = $this->get('app.youtube.service');

        $this->session->set('url', $request->get('url'));
        $this->session->set('business', $request->get('business'));
        return $this->redirect($youtubeService->auth());
    }

    /**
     * @Route("/youtube/oauth2callback", name="youtube-oauth2callback")
     * @Security("has_role('ROLE_USER')")
     */
    public function oauth2callbackAction(Request $request)
    {
        /**
         * @var YoutubeService $youtubeService
         */
        $youtubeService = $this->get('app.youtube.service');

        $accessTokeData = $youtubeService->getAccessToken($request->get('code'));

        $youtubeService->createUpdateYoutubeAccount($accessTokeData);

        return $this->redirect($this->generateUrl('social-network-posts', ['business' => $this->session->get('business')]) . '#youtube');
    }

    /**
     * @Route("/youtube/video/delete", name="youtube-video-delete")
     * @Security("has_role('ROLE_USER')")
     */
    public function deleteActions(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);

        /**
         * @var YoutubeService $youtubeService
         */
        $youtubeService = $this->get('app.youtube.service');

        $youtubeAccount = $this->findOneBy('SingAppBundle:YoutubeAccount', ['user' => $this->getUser()->getId(), 'business' => $currentBusiness->getId()]);

        try {
            $youtubeService->deleteVideo($youtubeAccount, $request->get('videoId'));
            return $this->redirect($this->generateUrl('social-network-posts', ['business' => $this->session->get('business')]) . '#youtube');
        } catch (OAuthCompanyException $e) {
            return $this->redirect($this->generateUrl('social-network-posts', ['business' => $this->session->get('business'), 'error' => $e->getMessage()]) . '#youtube');
        }
    }

    /**
     * @Route("/youtube/inside-post-delete/{youtubePost}", name="youtube-video-inside-delete")
     */
    public function deletePostAction(YoutubePost $youtubePost, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($youtubePost);
        $em->flush();
        return $this->redirectToRoute('youtube-post', $request->query->all());
    }

    /**
     * @Route(" / youtube / post", name="youtube-post")
     */
    public function postAction(Request $request)
    {
        /**
         * @var BusinessInfo $currentBusiness
         */
        $currentBusiness = $this->getCurrentBusiness($request);
        $user = $this->getUser();
        /**
         * @var YoutubeService $youtubeService
         */
        $youtubeService = $this->get('app.youtube.service');
        $youtubeAccount = $this->findOneBy('SingAppBundle:YoutubeAccount', ['user' => $user->getId(), 'business' => $currentBusiness->getId()]);
        $channels = $youtubeService->getChannel($youtubeAccount);
        $youtubeForm = $this->youtubePostForm($request, $channels)->createView();
        $youtubePosts = $posts = $this->findBy('SingAppBundle:Post', ['user' => $user->getId(), 'business' => $currentBusiness->getId(), 'socialNetwork' => self::SERVICE_NAME], ['postDate' => 'DESC']);

        $params = [
            'businesses' => $this->getBusinesses(),
            'form' => $youtubeForm,
            'posts' => $youtubePosts,
            'account' => $youtubeAccount,
            'service' => self::SERVICE_NAME,
            'massage' => self::SERVICE_MASSAGE,
            'currentBusiness' => $currentBusiness,
            'canDelete' => true
        ];

        return $this->render('@SingApp/socialNetworkPosts/index.html.twig', $params);
    }
}