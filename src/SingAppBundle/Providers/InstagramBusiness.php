<?php

namespace SingAppBundle\Providers;


use Doctrine\ORM\EntityManagerInterface;
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

    /**
     * InstagramBusiness constructor.
     * @param EntityManagerInterface $entityManager
     * @throws OAuthCompanyException
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    public function auth(User $user, BusinessInfo $business)
    {
        $settings = $this->getSettingData($user, $business);
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $this->ig = new Instagram($settings->debug, $settings->runcatedDebug);
        try {
            $this->ig->login($settings->username, $settings->password);
            return $this;
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function createAccount(BusinessInfo $business, InstagramAccount $instagram)
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
    protected function getSettingData(User $user, BusinessInfo $business)
    {
        $instagram = $this->getIstagramSetting($user, $business);
        $data = new \stdClass();
        $data->debug =false;
        $data->runcatedDebug = false;
        $data->username = $instagram->getName();
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
    protected function getClientData()
    {
        return $this->em->getRepository('SingAppBundle:BusinessInfo')->findOneBy(['user' => $this->user->getId()]);
    }

    protected function setUserData(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return \stdClass
     */
    protected function getFormatDataToSave()
    {
        $data = new \stdClass();
        $data->url = $this->getClientData()->getWebsite();
        $data->phone = $this->getClientData()->getPhoneNumber();
        $data->name = $this->getClientData()->getName();
        $data->biographi = $this->getClientData()->getDescription();
        $data->email = 'jo@cubeonline.com.au';
        $data->gender = 3;

        return $data;
    }

    /**
     * @throws OAuthCompanyException
     */
    public function updateAccount()
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
        }catch (InternalException $e){
            throw new OAuthCompanyException($e->getMessage());
        }
    }



}