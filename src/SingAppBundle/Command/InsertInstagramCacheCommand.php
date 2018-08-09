<?php

namespace SingAppBundle\Command;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Providers\InstagramBusiness;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InsertInstagramCacheCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('insert:instagram:cache')
            ->setDescription('Update service')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED)
            ->addArgument('account', InputArgument::REQUIRED, 'service account');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var InstagramBusiness $instagram
         */
        $instagram = $this->getContainer()->get('instagram_provider');

        $user = $this->getAccountById($input->getArgument('account'))->getUser();
        $business = $this->getAccountById($input->getArgument('account'))->getBusiness();
        $medias = $instagram->newAuth($user, $business)->authInst()->getMedias($user->getUsername());
        $cache = new FilesystemCache();
        $hashMedias = hash('ripemd160', 'facebook_medias.' . $business->getId() . 'user' . $user->getId());
        $cache->set($hashMedias, $medias);
    }

    private function getAccountById($id)
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $repository = $em->getRepository('SingAppBundle:SocialNetworkAccount');

        $account = $repository->findOneBy(['id' => $id]);

        return $account;
    }
}