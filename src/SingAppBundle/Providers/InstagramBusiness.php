<?php

namespace SingAppBundle\Providers;


use Doctrine\ORM\EntityManagerInterface;
use InstagramAPI\Exception\BadRequestException;
use InstagramAPI\Exception\InternalException;
use InstagramAPI\Instagram;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;

class InstagramBusiness
{
    protected $ig;
    protected $em;
    /**
     * @var User $business
     */
    protected $user;
    private $business;

    /**
     * InstagramBusiness constructor.
     * @param EntityManagerInterface $entityManager
     * @throws OAuthCompanyException
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    public function auth(User $user, BusinessInfo $business, InstagramAccount $account = null)
    {
        $this->user = $user;
        $this->business = $business;
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $settings = $this->getSettingData($user, $business, $account);
        $this->ig = new Instagram($settings->debug, $settings->runcatedDebug);
        try {
            $this->ig->login($settings->username, $settings->password);
            return $this;
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function createUpdateAccount(BusinessInfo $business, InstagramAccount $instagram)
    {
        $createdDate = new \DateTime();

        $instagram->setCreated($createdDate);
        $instagram->setBusiness($business);

        $this->em->persist($instagram);
        $this->em->flush();
    }

    /**
     * @return \stdClass
     */
    protected function getSettingData(User $user, BusinessInfo $business, InstagramAccount $account = null)
    {
        if(null === $account) {
            $instagram = $this->getIstagramSetting($user, $business);
        }else{
            $instagram = $account;
        }
        $data = new \stdClass();
        $data->debug = false;
        $data->runcatedDebug = false;
        $data->username = $instagram->getLogin();
        $data->password = $instagram->getPassword();
        return $data;
    }

    public function getIstagramSetting(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:InstagramAccount');
        $istagram = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $istagram;
    }

    /**
     * @return null|BusinessInfo
     */
    protected function getCurentBusinessData()
    {
        return $this->business;
    }

    /**
     * @return \stdClass
     */
    protected function getFormatDataToSave()
    {
        $data = new \stdClass();
        $data->url = $this->getCurentBusinessData()->getWebsite();
        $data->phone = $this->getCurentBusinessData()->getPhoneNumber();
        $data->name = $this->getCurentBusinessData()->getName();
        $data->biographi = $this->getCurentBusinessData()->getDescription();
        $data->email = $this->getCurentBusinessData()->getEmail();;
        $data->gender = 3;

        return $data;
    }

    /**
     * @throws OAuthCompanyException
     */
    public function updateIstagramAccount()
    {
        try {
            $this->ig->account->editProfile(
                $this->getFormatDataToSave()->url,
                $this->getFormatDataToSave()->phone,
                $this->getFormatDataToSave()->name,
                $this->getFormatDataToSave()->biographi,
                $this->getFormatDataToSave()->email,
                $this->getFormatDataToSave()->gender
            );
        } catch (BadRequestException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }


}