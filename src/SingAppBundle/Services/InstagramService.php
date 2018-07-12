<?php


namespace SingAppBundle\Services;


use SingAppBundle\Entity\InstagramAccount;
use SingAppBundle\Entity\InstagramPost;
use SingAppBundle\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use InstagramAPI\Response\ConfigureResponse;

class InstagramService
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
            } catch (\Exception $e) {
                echo 'Something went wrong: ' . $e->getMessage() . "\n";
                exit(0);
            }

            $mediaId = $post->getMediaId();

            if ($mediaId) {
                $likers = $ig->media->getLikersChrono($mediaId)->getUsers();
            } else {
                $likers = null;
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
            } catch (\Exception $e) {
                echo 'Something went wrong: ' . $e->getMessage() . "\n";
                exit(0);
            }

            $mediaId = $post->getMediaId();

            if ($mediaId) {
                $comments = $ig->media->getComments($mediaId)->getComments();
            } else {
                $comments = null;
            }
        }

        return $comments;
    }

    public function uploadPost(InstagramPost $instagramPost)
    {
        if ($instagramPost->getPhotos()->count() > 1) {
            $this->uploadMultiplePhotos($instagramPost);
        } else {
            $this->uploadSinglePhoto($instagramPost);
        }
    }

    private function uploadMultiplePhotos(InstagramPost $instagramPost)
    {
        $instagramAccount = $instagramPost->getAccount();
        $debug = false;
        $truncatedDebug = false;

        $photos = $this->getPhotos($instagramPost);

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

        try {
            $ig->login($instagramAccount->getLogin(), $instagramAccount->getPassword());
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();
            echo 'Something went wrong: ' . $e->getMessage() . "\n";
            exit(0);
        }

        $mediaOptions = [
            'targetFeed' => \InstagramAPI\Constants::FEED_TIMELINE_ALBUM,
            // Uncomment to expand media instead of cropping it.
            //'operation' => \InstagramAPI\Media\InstagramMedia::EXPAND,
        ];

        foreach ($photos as &$item) {
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
            $response = $ig->timeline->uploadAlbum($photos, ['caption' => $instagramPost->getCaption()]);

            $instagramPost->setStatus('posted');
            $instagramPost->setMediaId($response->getMedia()->getId());

            $this->em->persist($instagramPost);
            $this->em->flush();
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();
            echo 'Something went wrong: ' . $e->getMessage() . "\n";
        }
    }

    private function uploadSinglePhoto(InstagramPost $instagramPost)
    {
        $instagramAccount = $instagramPost->getAccount();
        $debug = true;
        $truncatedDebug = false;

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
        try {
            $ig->login($instagramAccount->getLogin(), $instagramAccount->getPassword());
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();
            echo 'Something went wrong: ' . $e->getMessage() . "\n";
            exit(0);
        }
        try {
            $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($this->webDir . '/' . $instagramPost->getPhotos()[0]->getPath());

            /**
             * @var ConfigureResponse $response
             */
            $response = $ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $instagramPost->getCaption()]);
            $instagramPost->setMediaId($response->getMedia()->getId());

            $instagramPost->setStatus('posted');
            $this->em->persist($instagramPost);
            $this->em->flush();
        } catch (\Exception $e) {
            $instagramPost->setStatus('failed');
            $this->em->persist($instagramPost);
            $this->em->flush();

            echo 'Something went wrong: ' . $e->getMessage() . "\n";
        }
    }

    private function getPhotos(InstagramPost $instagramPost)
    {
        $photos = [];

        /**
         * @var Photo $photo
         */
        foreach ($instagramPost->getPhotos() as $photo) {
            $photoPath = $this->webDir . '/' . $photo->getPath();

            $photos[] = [
                'type' => 'photo',
                'file' => $photoPath,
            ];
        }

        return $photos;
    }


}