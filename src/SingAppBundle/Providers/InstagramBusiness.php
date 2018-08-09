<?php

namespace SingAppBundle\Providers;


use Doctrine\ORM\EntityManagerInterface;
use InstagramAPI\Exception\BadRequestException;
use InstagramAPI\Exception\InternalException;
use InstagramAPI\Instagram;
use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Exception\InstagramException;
use InstagramAPI\Exception\InstagramException as InstagramApiException;
use InstagramScraper\Instagram as InstagramScraper;
use InstagramScraper\Model\Comment;
use InstagramScraper\Model\Like;
use InstagramScraper\Model\Media;
use JMS\JobQueueBundle\Entity\Job;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Component\Cache\Simple\FilesystemCache;

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
    /**
     * @var BusinessInfo $business
     */
    private $business;
    private $account;
    /**
     * @var Instagram $igNew
     */
    private $igNew;

    /**
     * InstagramBusiness constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

    }

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @param InstagramAccount|null $account
     * @return $this
     * @throws OAuthCompanyException
     */
    public function auth(User $user, BusinessInfo $business, InstagramAccount $account = null)
    {
        $this->user = $user;
        $this->business = $business;
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $this->account = $this->getSettingData($this->user, $this->business, $account);
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

        $this->createInstagramJobs('insert:instagram:cache', $instagram->getId());
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

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @return $this
     * @throws OAuthCompanyException
     * @throws \phpFastCache\Exceptions\phpFastCacheDriverCheckException
     */
    public function newAuth(User $user, BusinessInfo $business)
    {
        $this->user = $user;
        $this->business = $business;
        $this->account = $this->getSettingData($user, $business);
        $this->ig = InstagramScraper::withCredentials($this->account->username, $this->account->password);
        try {
            $this->ig->login();
            return $this;
        } catch (InstagramAuthException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function getMedias($username)
    {
        return $this->ig->getMedias($username);
    }

    /**
     * @return Media[]|mixed|null
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     */
    public function getInfoNewScraper()
    {
        $cache = new FilesystemCache();
        $hashMedia = hash('ripemd160', 'instagram_media.' .  $this->business->getId() . 'user' . $this->user->getId());
        var_dump($cache->has($hashMedia));
        if($cache->has($hashMedia)){
            var_dump($cache->get($hashMedia));
            $medias = $cache->get($hashMedia);
        }else {
            $medias = $this->getMedias($this->account->username);
        }
        return $medias;
    }

    /**
     * @param Media $media
     * @return int|mixed|null
     */
    public function getLikesByMedia(Media $media)
    {
        return $likes = $this->igNew->media->getLikers($media->getId())->getUserCount();
    }

    private function createInstagramJobs($name, $accountId)
    {
        $job = new Job($name, [$accountId]);
        $this->em->persist($job);
        $this->em->flush();
    }

    /**
     * @return $this
     * @throws OAuthCompanyException
     */
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

    public function getAccount()
    {
        $cache = new FilesystemCache();
        $hash = hash('ripemd160', 'facebook_account_current_user.' . $this->business->getId() . 'user' . $this->user->getId());
        try {
            $user = $this->igNew->account->getCurrentUser();
            $cache->set($hash, $user);
        }catch (InstagramApiException $e) {
            $user = $cache->get($hash);
        }
        return $user;
    }

    /**
     * @return array
     * @throws \InstagramScraper\Exception\InstagramNotFoundException
     */
    public function getAllComments()
    {
        $comments = [];
        /**
         * @var Media $media
         * @var Comment $comment
         */
        foreach ($this->getInfoNewScraper() as $media) {
            $comments[$media->getId()] = $this->igNew->media->getComments($media->getId())->getComments();
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

    /**
     * @param $mediaId
     * @param null $commentId
     * @param $text
     * @throws OAuthCompanyException
     */
    public function addComment($mediaId, $commentId = null, $text)
    {
        try {
            if (null === $commentId) {
                $this->ig->media->comment($mediaId, $text);
            } else {
                $this->ig->media->comment($mediaId, '@' . $this->ig->account->getCurrentUser()->getUser()->getUsername() . ' ' . $text, $commentId);
            }
        }catch (\Exception $e){
            throw new OAuthCompanyException($e->getMessage());
        }
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
            $likesCount += $this->getLikesByMedia($media);
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