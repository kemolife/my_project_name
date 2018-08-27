<?php


namespace SingAppBundle\Services;


use InstagramAPI\Exception\BadRequestException;
use InstagramAPI\Exception\InstagramException;
use InstagramScraper\Instagram;
use JMS\JobQueueBundle\Entity\Job;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\Images;
use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use Doctrine\ORM\EntityManagerInterface;
use InstagramAPI\Response\ConfigureResponse;
use SingAppBundle\Entity\Media;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\interfaces\BaseInterface;

class InstagramService implements BaseInterface
{
    private $em;

    private $webDir;

    public function __construct(EntityManagerInterface $entityManager, $webDir)
    {
        $this->em = $entityManager;

        $this->webDir = $webDir;
    }

    public function getInfo(InstagramPost $post)
    {
        $info = null;

        if ($post->getAccount() instanceof InstagramAccount) {
            $username = $post->getAccount()->getLogin();
            $password = $post->getAccount()->getPassword();
            $debug = false;
            $truncatedDebug = false;
            \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

            $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

            try {
                $ig->login($username, $password);
            } catch (\Exception $e) {
                echo 'Something went wrong: ' . $e->getMessage() . "\n";
                exit(0);
            }

            $mediaId = $post->getMediaId();

            if ($mediaId) {
                $info = $ig->media->getInfo($mediaId)->getItems()[0];
            } else {
                $info = null;
            }
        }


        return $info;

    }

    public function getLikers(InstagramPost $post)
    {
        $likers = null;

        if ($post->getAccount() instanceof InstagramAccount) {
            $username = $post->getAccount()->getLogin();
            $password = $post->getAccount()->getPassword();
            $debug = false;
            $truncatedDebug = false;
            \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

            $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

            try {
                $ig->login($username, $password);

                $mediaId = $post->getMediaId();

                if ($mediaId) {
                    $likers = $ig->media->getLikersChrono($mediaId)->getUsers();
                } else {
                    $likers = null;
                }
            } catch (\Exception $e) {
                throw new OAuthCompanyException($e->getMessage());
            }
        }

        return $likers;
    }

    public function getComments(InstagramPost $post)
    {
        $comments = null;

        if ($post->getAccount() instanceof InstagramAccount) {
            $username = $post->getAccount()->getLogin();
            $password = $post->getAccount()->getPassword();
            $debug = false;
            $truncatedDebug = false;
            \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

            $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

            try {
                $ig->login($username, $password);

                $mediaId = $post->getMediaId();

                if ($mediaId) {
                    $comments = $ig->media->getComments($mediaId)->getComments();
                } else {
                    $comments = null;
                }
            } catch (\Exception $e) {
                throw new OAuthCompanyException($e->getMessage());
            }
        }

        return $comments;
    }

    public function uploadPost(InstagramPost $instagramPost)
    {
        if (count($instagramPost->getMedia()) > 1) {
            $this->uploadMultiplePhotos($instagramPost);
        } else {
            $this->uploadSingleMedia($instagramPost);
        }
        $this->createInstagramJobs('insert:instagram:cache', $instagramPost->getAccount()->getId());
    }

    private function createInstagramJobs($name, $accountId)
    {
        $job = new Job($name, [$accountId]);
        $job->setExecuteAfter(new \DateTime(time()+300));
        $this->em->persist($job);
        $this->em->flush();
    }

    private function uploadMultiplePhotos(InstagramPost $instagramPost)
    {
        $instagramAccount = $instagramPost->getAccount();
        $debug = false;
        $truncatedDebug = false;

        $media = $this->getMedia($instagramPost);

        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

        try {
            $ig->login($instagramAccount->getLogin(), $instagramAccount->getPassword());
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();

            throw new OAuthCompanyException($e->getMessage());

        }

        $mediaOptions = [
            'targetFeed' => \InstagramAPI\Constants::FEED_TIMELINE_ALBUM,
            // Uncomment to expand media instead of cropping it.
            //'operation' => \InstagramAPI\Media\InstagramMedia::EXPAND,
        ];

        foreach ($media as &$item) {
            /** @var \InstagramAPI\Media\InstagramMedia|null $validMedia */
            $validMedia = null;
            switch ($item['type']) {
                case 'photo':
                    $validMedia = new \InstagramAPI\Media\Photo\InstagramPhoto($item['file'], $mediaOptions);
                    break;
                case 'video':
                    $validMedia = new \InstagramAPI\Media\Video\InstagramVideo($item['file'], $mediaOptions);
                    break;
                default:
                    // Ignore unknown media type.
            }
            if ($validMedia === null) {
                continue;
            }
            try {
                $item['file'] = $validMedia->getFile();
                // We must prevent the InstagramMedia object from destructing too early,
                // because the media class auto-deletes the processed file during their
                // destructor's cleanup (so we wouldn't be able to upload those files).
                $item['__media'] = $validMedia; // Save object in an unused array key.
            } catch (\Exception $e) {
                continue;
            }
            if (!isset($mediaOptions['forceAspectRatio'])) {
                // Use the first media file's aspect ratio for all subsequent files.
                /** @var \InstagramAPI\Media\MediaDetails $mediaDetails */
                $mediaDetails = $validMedia instanceof \InstagramAPI\Media\Photo\InstagramPhoto
                    ? new \InstagramAPI\Media\Photo\PhotoDetails($item['file'])
                    : new \InstagramAPI\Media\Video\VideoDetails($item['file']);
                $mediaOptions['forceAspectRatio'] = $mediaDetails->getAspectRatio();
            }
        }
        unset($item);

        try {
            /**
             * @var ConfigureResponse $response
             */
            $response = $ig->timeline->uploadAlbum($media, ['caption' => $instagramPost->getCaption()]);

            $instagramPost->setStatus('posted');
            $instagramPost->setMediaId($response->getMedia()->getId());

            $this->em->persist($instagramPost);
            $this->em->flush();
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    private function uploadSingleMedia(InstagramPost $instagramPost)
    {
        $instagramAccount = $instagramPost->getAccount();
        $debug = false;
        $truncatedDebug = false;

        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

        try {
            $ig->login($instagramAccount->getLogin(), $instagramAccount->getPassword());
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();

            throw new OAuthCompanyException($e->getMessage());

        }
        try {
            $mimeType = $this->_mime_content_type($this->webDir . '/' . $instagramPost->getMedia()[0]->getPath());

            if (strpos($mimeType, 'image') !== false) {
                $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($this->webDir . '/' . $instagramPost->getMedia()[0]->getPath());

                /**
                 * @var ConfigureResponse $response
                 */
                $response = $ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $instagramPost->getCaption()]);
                $instagramPost->setMediaId($response->getMedia()->getId());

                $instagramPost->setStatus('posted');
                $this->em->persist($instagramPost);
                $this->em->flush();
            } elseif (strpos($mimeType, 'video') !== false) {
                \InstagramAPI\Utils::$ffprobeBin = '/usr/bin/ffprobe';
                $mediaPath = $this->webDir . '/' . $instagramPost->getMedia()[0]->getPath();

                $videoDuration = exec('ffprobe -i '.$mediaPath.' -show_entries format=duration -v quiet -of csv="p=0"');

                if ($videoDuration > 60) {

                    $newMediaPath = str_replace(basename($mediaPath), 'cut_'.basename($mediaPath), $mediaPath);

                    exec('ffmpeg -i '.$mediaPath.' -ss 00:00:00 -t 00:00:59 -strict -2 '.$newMediaPath, $output);


                    $mediaPath = $newMediaPath;
                }

                $video = new \InstagramAPI\Media\Video\InstagramVideo($mediaPath);

                /**
                 * @var ConfigureResponse $response
                 */
                $response = $ig->timeline->uploadVideo($video->getFile(), ['caption' => $instagramPost->getCaption()]);
                $instagramPost->setMediaId($response->getMedia()->getId());

                $instagramPost->setStatus('posted');
                $this->em->persist($instagramPost);
                $this->em->flush();
            }

        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();

            throw new OAuthCompanyException($e->getMessage());
        }
    }

    private function getMedia(InstagramPost $instagramPost)
    {
        $photos = [];

        /**
         * @var Media $media
         */
        foreach ($instagramPost->getMedia() as $media) {
            $mediaPath = $this->webDir . '/' . $media->getPath();
            $mimeType = $this->_mime_content_type($mediaPath);
            $type = 'photo';

            if (strpos($mimeType, 'video') !== false) {
                $type = 'video';

                $videoDuration = exec('ffprobe -i '.$mediaPath.' -show_entries format=duration -v quiet -of csv="p=0"');

                if ($videoDuration > 60) {

                    $newMediaPath = str_replace(basename($mediaPath), 'cut_'.basename($mediaPath), $mediaPath);
                    exec('ffmpeg -i '.$mediaPath.' -ss 00:00:00 -t 00:01:00 -strict -2 '.$newMediaPath, $output);

                    $mediaPath = $newMediaPath;
                }
            }

            $photos[] = [
                'type' => $type,
                'file' => $mediaPath,
            ];
        }

        return $photos;
    }


    private function _mime_content_type($filename)
    {
        # Returns the system MIME type (as defined in /etc/mime.types) for the filename specified.
        #
        # $file - the filename to examine
        static $types;
        if (!isset($types))
            $types = $this->system_extension_mime_types();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!$ext)
            $ext = $filename;
        $ext = strtolower($ext);

        return isset($types[$ext]) ? $types[$ext] : null;
    }

    private function system_extension_mime_types()
    {
        # Returns the system MIME type mapping of extensions to MIME types, as defined in /etc/mime.types.
        $out = array();
        $file = fopen('/etc/mime.types', 'r');
        while (($line = fgets($file)) !== false) {
            $line = trim(preg_replace('/#.*/', '', $line));
            if (!$line)
                continue;
            $parts = preg_split('/\s+/', $line);
            if (count($parts) == 1)
                continue;
            $type = array_shift($parts);
            foreach ($parts as $part)
                $out[$part] = $type;
        }
        fclose($file);
        return $out;
    }

    public function deletePost(InstagramPost $post)
    {
        $instagramAccount = $post->getAccount();
        $mediaId = $post->getMediaId();
        $debug = false;
        $truncatedDebug = false;
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {
            $ig->login($instagramAccount->getLogin(), $instagramAccount->getPassword());
            if ($mediaId) {
                $ig->media->delete($mediaId);
            } else {
                throw new OAuthCompanyException('Media not found');
            }
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    /**
     * @param SocialNetworkAccount $instagramAccount
     * @param BusinessInfo $business
     * @throws OAuthCompanyException
     */
    public function editAccount(SocialNetworkAccount $instagramAccount, BusinessInfo $business)
    {
        $debug = false;
        $truncatedDebug = false;
        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {
            $ig->login($instagramAccount->getName(), $instagramAccount->getPassword());
            $ig->account->editProfile(
                $business->getWebsite(),
                $business->getPhoneNumber(),
                $business->getName(),
                $business->getDescription(),
                $business->getEmail(),
                3
            );
        } catch (InstagramException $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public function removePost(InstagramPost $instagramPost)
    {
        $instagramAccount = $instagramPost->getAccount();
        $debug = false;
        $truncatedDebug = false;

        \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;
        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

        try {
            $ig->login($instagramAccount->getLogin(), $instagramAccount->getPassword());

            $ig->media->delete($instagramPost->getMediaId());
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }

    }

    public function searchBusiness($account, BusinessInfo $business)
    {
        $searchObject = new \StdClass();
        $searchObject->status = self::STATUS_FALSE;
        $searchObject->name = null;
        $searchObject->address = null;
        $searchObject->phone = null;
        if($account instanceof InstagramAccount && null !== $account->getLogin() && null !== $account->getPassword()){
            $debug = false;
            $truncatedDebug = false;
            \InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

            $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
            try {
                $ig->login($account->getLogin(), $account->getPassword());
                $user = $ig->account->getCurrentUser()->getUser();
                $searchObject->status = self::STATUS_TRUE;
                $searchObject->name = $user->getFullName();
                $searchObject->address = $user->getAddressStreet();
                $searchObject->phone = $user->getPhoneNumber();
            } catch (\Exception $e) {
            }
        }

        return $searchObject;
    }
}