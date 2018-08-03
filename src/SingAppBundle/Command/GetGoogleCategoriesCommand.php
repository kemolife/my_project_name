<?php

namespace SingAppBundle\Command;

use Doctrine\ORM\EntityManager;
use SingAppBundle\Entity\AdditionalCategoriesBusinessInfo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetGoogleCategoriesCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('app:google:cat')
            ->setDescription('Save data from google');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->saveCategory();
    }

    private function getCAtegoryList()
    {
        $category = file_get_contents('categories_au.json');
        return json_decode($category);
    }

    private function saveCategory()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        foreach ($this->getCAtegoryList()->categories as $item) {
            if ($item instanceof \stdClass) {
                $category= new AdditionalCategoriesBusinessInfo();
                $category->setName($item->displayName);
                $category->setCategoryId($item->categoryId);

                $em->persist($category);
                print_r('+');
            }
        }
        $em->flush();
    }
}