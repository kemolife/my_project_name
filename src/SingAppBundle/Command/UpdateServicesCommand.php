<?php

namespace SingAppBundle\Command;

use Doctrine\ORM\EntityManager;
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
        $serviceAccount = $this->getAccountById($input->getArgument('account'));
        try {
            $service = $this->getContainer()->get('app.' . strtolower(stristr((new ReflectionClass($serviceAccount))->getShortName(), 'Account', true)) . '.service');

            $service->editAccount($serviceAccount, $serviceAccount->getBusiness());
        } catch (OAuthCompanyException $e) {
            throw $e;
        } catch (\Exception $e) {

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