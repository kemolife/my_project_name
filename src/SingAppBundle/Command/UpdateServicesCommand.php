<?php

namespace SingAppBundle\Command;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateServicesCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('app:update:service')
            ->setDescription('Update service')
            ->addOption('jms-job-id', null, InputOption::VALUE_REQUIRED)
            ->addArgument('account', InputArgument::REQUIRED, 'service account');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $serviceAccount = $this->getAccountById($input->getArgument('account'));

            $service = $this->getContainer()->get('apps.' . strtolower(stristr((new ReflectionClass($serviceAccount))->getShortName(), 'Account', true)) . '.service');
            $service->editAccount($serviceAccount, $serviceAccount->getBusiness());
        } catch (OAuthCompanyException $e) {
            $this->getContainer()->get('logger')->error($e->getMessage(), array('exception' => $e->getMessage()));
        } catch (\Exception $e) {
            $this->getContainer()->get('logger')->error($e->getMessage(), array('exception' => $e->getMessage()));
        }
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