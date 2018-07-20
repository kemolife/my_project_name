<?php

namespace SingAppBundle\Providers;


use Doctrine\ORM\EntityManagerInterface;
use InstagramAPI\Exception\BadRequestException;
use InstagramAPI\Exception\InternalException;
use InstagramAPI\Instagram;
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Instagram as InstagramScraper;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Like;
use InstagramScraper\Model\Media;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;

class InstagramBusiness
{
    /**
     * @var InstagramScraper|Instagram $ig
     */
    protected $ig;
    protected $em;
    /**
     * @var User $business
     */
    protected $user;
    private $business;
    private $account;
    /**
     * @var Instagram $igNew
     */
    private $igNew;

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
        $this->account = $this->getSettingData($user, $business, $account);
        $this->ig = new Instagram($this->account->debug, $this->account->runcatedDebug);
        try {
            $this->ig->login($this->account->username, $this->account->password);
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
        if (null === $account) {
            $instagram = $this->getIstagramAccount($user, $business);
        } else {
            $instagram = $account;
        }
        $data = new \stdClass();
        $data->debug = false;
        $data->runcatedDebug = false;
        $data->username = $instagram->getLogin();
        $data->password = $instagram->getPassword();
        return $data;
    }

    public function getIstagramAccount(User $user, BusinessInfo $business)
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

    public function newAuth(User $user, BusinessInfo $business)
    {
        $this->account = $this->getSettingData($user, $business);
        $this->ig = InstagramScraper::withCredentials($this->account->username, $this->account->password);
        try {
            $this->ig->login();
            return $this;
        } catch (InstagramException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getInfoNewScraper()
    {
        $account = $this->ig->getMedias($this->account->username);
        return $account;
    }

    public function authInst()
    {
        $debug = false;
        $truncatedDebug = false;
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $this->igNew = new Instagram($debug, $truncatedDebug);
        try {
            $this->igNew->login($this->account->username, $this->account->password);
            return $this;
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getAllComments()
    {
        $comments = [];
        /**
         * @var Media $media
         * @var Comment $comment
         */
        foreach ($this->getInfoNewScraper() as $media) {
            $comments[] = $this->igNew->media->getComments($media)->getComments();
        }
        return $comments;
    }

    public function getCommentReplies($media, $commentId)
    {
        if ($media) {
            $comments = $this->igNew->media->getCommentReplies($media, $commentId);
        } else {
            $comments = null;
        }

        return $comments;
    }

    public function addComment($mediaId, $commentId = null, $text)
    {
        if (null === $commentId) {
            $this->igNew->media->comment($mediaId, $text);
            $status = true;
        }else{
            $this->igNew->media->comment($mediaId, '@'.$this->ig->account->getCurrentUser()->getUser()->getUsername().' '.$text, $commentId);
            $status = true;
        }
        return $status;
    }

    public function getAllLikesCount()
    {
        $likesCount = 0;
        /**
         * @var Media $media
         */
        foreach ($this->getInfoNewScraper() as $media) {
            /**
             * @var Like $like
             */
            foreach ($this->ig->getMediaLikesByCode($media->getShortCode()) as $key => $like) {
                $likesCount++;
            }
        }
        return $likesCount;
    }

    public function getAllLikesByUser()
    {
        $likes = [];
        /**
         * @var Media $media
         */
        foreach ($this->getInfoNewScraper() as $media) {
            /**
             * @var Like $like
             */
            foreach ($this->ig->getMediaLikesByCode($media->getShortCode()) as $key => $like) {
                $likes[$key]['user'] = $like->getUserName();
            }
        }
        return $likes;
    }


}