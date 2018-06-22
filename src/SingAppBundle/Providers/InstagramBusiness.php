<?php

namespace SingAppBundle\Providers;


use Doctrine\ORM\EntityManagerInterface;
use InstagramAPI\Exception\InternalException;
use InstagramAPI\Instagram;

class InstagramBusiness
{
    protected $ig;
    protected $entityManager;

    /**
     * InstagramBusiness constructor.
     * @param EntityManagerInterface $entityManager
     * @throws OAuthCompanyException
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $this->ig = new Instagram($this->getSettingData()->debug, $this->getSettingData()->runcatedDebug);
        try {
            $this->ig->login($this->getSettingData()->username, $this->getSettingData()->password);
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }

    }

    /**
     * @return \stdClass
     */
    protected function getSettingData()
    {
        $data = new \stdClass();
        $data->debug =true;
        $data->runcatedDebug = false;
        $data->username = 'clinic_51';
        $data->password = 'S3ptember';
        return $data;
    }

    /**
     * @return null|object
     */
    protected function getClientData()
    {
        return $this->entityManager->getRepository('SingAppBundle:BusinessInfo')->findOneBy(['user' => 1]);
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
        $data->email = 'mktk76@yahoo.com';
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