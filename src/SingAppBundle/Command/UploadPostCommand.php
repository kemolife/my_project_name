<?php

namespace SingAppBundle\Command;

use SingAppBundle\Entity\GooglePost;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\YoutubePost;
use SingAppBundle\Services\GoogleService;
use SingAppBundle\Services\InstagramService;
use Doctrine\ORM\EntityManager;
use SingAppBundle\Services\YoutubeService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UploadPostCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('app:post:upload')
            ->setDescription('Upload posts')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED)
            ->addArgument('post', InputArgument::REQUIRED, 'The id of the post.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var InstagramPost $post
         */
        $post = $this->getPostById($input->getArgument('post'));

        $postDate = $post->getPostDate();

        if ($post->getTimezoneOffset() != 0) {
            $postDate = $postDate->modify($post->getTimezoneOffset().' hours');
        }

        if ($post instanceof InstagramPost && $postDate <= new \DateTime('now')) {
            /**
             * @var InstagramService $instagramService
             */
            $instagramService = $this->getContainer()->get('app.instagram.service');

            $instagramService->uploadPost($post);
        }
        elseif ($post instanceof GooglePost && $postDate <= new \DateTime('now')) {
            /**
             * @var GoogleService $googleService
             */
            $googleService = $this->getContainer()->get('app.google.service');

            $googleService->createPost($post);
        }
        elseif ($post instanceof YoutubePost && $postDate <= new \DateTime('now')) {
            /**
             * @var YoutubeService $youtubeService
             */
            $youtubeService = $this->getContainer()->get('app.youtube.service');

            $youtubeService->createVideo($post);
        }
    }


    private function getPostById($id)
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $repository = $em->getRepository('AppBundle:InstagramPost');

        $post = $repository->findOneBy(['id' => $id]);

        return $post;
    }
}