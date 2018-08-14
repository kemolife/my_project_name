<?php
/**
 * Created by PhpStorm.
 * User: xubuntu
 * Date: 09.08.18
 * Time: 12:13
 */

namespace SingAppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;
use SingAppBundle\Entity\YoutubeAccount;
use SingAppBundle\Entity\YoutubePost;
use SingAppBundle\Providers\Exception\OAuthCompanyException;

class YoutubeService
{
    private $em;
    private $domain;
    private $webDir;

    public function __construct(EntityManagerInterface $entityManager, $domain, $webDir)
    {
        $this->em = $entityManager;
        $this->domain = $domain;
        $this->webDir = $webDir;
    }

    private function clientSettings()
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret_youtube.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->setScopes([GOOGLE_SERVICE_YOUTUBE::YOUTUBE_FORCE_SSL]);
        $client->setRedirectUri($this->domain . '/youtube/oauth2callback');

        return $client;
    }

    private function clientAccountSettings(YoutubeAccount $youtubeAccount)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret_youtube.json');
        $client->setAccessToken($youtubeAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true); // incremental auth
        $client->setScopes([GOOGLE_SERVICE_YOUTUBE::YOUTUBE_FORCE_SSL]);

        return $client;
    }

    private function youtubeAndGoogleSettingsScope(YoutubeAccount $youtubeAccount)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret_youtube.json');
        $client->setAccessToken($youtubeAccount->getAccessToken());
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true); // incremental auth
        $client->setScopes([GOOGLE_SERVICE_YOUTUBE::YOUTUBE_FORCE_SSL, 'https://www.googleapis.com/auth/plus.business.manage']);

        return $client;
    }

    public function auth()
    {
        $client = $this->clientSettings();
        return $client->createAuthUrl();
    }

    public function authYoutube()
    {
        $client = $this->clientSettings();

        return $client->createAuthUrl();
    }

    public function getAccessToken($code)
    {

        $client = $this->clientSettings();

        return $client->authenticate($code);
    }

    public function refreshAccessToken(YoutubeAccount $youtubeAccount)
    {
        $client = $this->youtubeAndGoogleSettingsScope($youtubeAccount);

        $accessTokenData = $client->fetchAccessTokenWithRefreshToken($youtubeAccount->getRefreshToken());
        if (array_key_exists('access_token', $accessTokenData) && array_key_exists('refresh_token', $accessTokenData)) {
//            $oauthService = new \Google_Service_Oauth2($client);
//
//            $repository = $this->em->getRepository('SingAppBundle:YoutubeAccount');
//
//            $youtubeAccount = $repository->findBy(['googleId' => $oauthService->userinfo->get()->getId()]);
//
//            /**
//             * @var YoutubeAccount $account
//             */
//            foreach ($youtubeAccount as $account) {
//                $account->setAccessToken($accessTokenData['access_token']);
//                $account->setRefreshToken($accessTokenData['refresh_token']);
//                $account->setExpiresIn(new \DateTime('+ ' . $accessTokenData['expires_in'] . ' seconds'));
//
//                $this->em->persist($account);
//            }

            if ($youtubeAccount instanceof YoutubeAccount) {
                $youtubeAccount->setAccessToken($accessTokenData['access_token']);
                $youtubeAccount->setRefreshToken($accessTokenData['refresh_token']);
                $youtubeAccount->setExpiresIn(new \DateTime('+ ' . $accessTokenData['expires_in'] . ' seconds'));
//                $youtubeAccount->setGoogleId($oauthService->userinfo->get()->getId());

                $this->em->persist($youtubeAccount);

                $this->em->flush();
            }
        }

    }

    public function createUpdateYoutubeAccount($accessTokeData, $youtubeAccount = null)
    {
        if (array_key_exists('access_token', $accessTokeData)) {
            {
                $createdDate = new \DateTime();
                $createdDate->setTimestamp($accessTokeData['created']);
                if ($youtubeAccount === null) {
                    $youtubeAccount = new YoutubeAccount();
                }

                if (array_key_exists('refresh_token', $accessTokeData)) {
                    $youtubeAccount->setRefreshToken($accessTokeData['refresh_token']);
                }

                if ($youtubeAccount instanceof YoutubeAccount) {
                    $youtubeAccount->setAccessToken($accessTokeData['access_token']);
                    $youtubeAccount->setCreated($createdDate);
                    $youtubeAccount->setExpiresIn(new \DateTime('+ ' . $accessTokeData['expires_in'] . ' seconds'));

                    $this->em->persist($youtubeAccount);
                    $this->em->flush();
                }
            }

        }
    }

    public function getUploadVideos(YoutubeAccount $youtubeAccount)
    {
        $playListVideo = [];
        $client = $this->clientAccountSettings($youtubeAccount);
        $youtube = new Google_Service_YouTube($client);
        try {
            foreach ($youtube->channels->listChannels("snippet, contentDetails", ['mine' => true]) as $channel) {
                $idUpload = $channel->getContentDetails()->getRelatedPlaylists()->getUploads();
                $playListVideo = $youtube->playlistItems->listPlaylistItems("snippet, status, id, contentDetails", ['playlistId' => $idUpload]);
            };
            return $playListVideo;
        } catch (Google_Service_Exception $e) {
            try {
                $this->refreshAccessToken($youtubeAccount);
                $this->getUploadVideos($youtubeAccount);
            } catch (\Exception $e) {
                throw new OAuthCompanyException(json_encode($e->getMessage()));
            }
        }
    }

    public function createVideo(YoutubePost $youtubePost)
    {
        $thumbnails = [];
        $properties = [];
        $client = $this->clientAccountSettings($youtubePost->getAccount());
        $youtube = new Google_Service_YouTube($client);
        foreach ($youtubePost->getMedia() as $media) {
            $mediaPath = $this->webDir . '/' . $media->getPath();
            $mimeType = $this->_mime_content_type($mediaPath);
            if (strpos($mimeType, 'image') !== false) {
                $thumbnails = [
                    'snippet.thumbnails.default.url' => $this->webDir . '/' . $media->getPath(),
                ];
            }
            if (strpos($mimeType, 'video') !== false) {
                $mediaPath = $this->webDir . '/' . $media->getPath();
                $properties = [
                    'snippet.categoryId' => '22',
                    'snippet.defaultLanguage' => '',
                    'snippet.description' => $youtubePost->getCaption(),
                    'snippet.tags[]' => '',
                    'snippet.channelId' => $youtubePost->getChannelId(),
                    'snippet.title' => $youtubePost->getTitle(),
                    'status.embeddable' => '',
                    'status.license' => '',
                    'status.privacyStatus' => $youtubePost->getStatus(),
                    'status.publicStatsViewable' => ''
                ];
            }
        }

        if (!empty($thumbnails)) {
            $properties = array_merge($properties, $thumbnails);
        }

        $propertyObject = $propertyObject = $this->createResource($properties);
        try {
            $resource = new Google_Service_YouTube_Video($propertyObject);
            $client->setDefer(true);
            $request = $youtube->videos->insert('snippet,status,contentDetails, id', $resource);
            $client->setDefer(false);
            $response = $this->uploadMedia($client, $request, $mediaPath, 'video/*');
            $youtubePost->setVideoId($response->getId());
            $youtubePost->setStatus('posted');
            $this->em->persist($youtubePost);
            $this->em->flush();

        } catch (Google_Service_Exception $e) {
            try {
                $this->refreshAccessToken($youtubePost->getAccount());
                $this->createVideo($youtubePost);
            } catch (\Exception $e) {
                throw new OAuthCompanyException(json_encode($e->getMessage()));
            }
        }
    }

    private
    function uploadMedia($client, $request, $filePath, $mimeType)
    {
        // Specify the size of each chunk of data, in bytes. Set a higher value for
        // reliable connection as fewer chunks lead to faster uploads. Set a lower
        // value for better recovery on less reliable connections.
        $chunkSizeBytes = 1 * 1024 * 1024;

        // Create a MediaFileUpload object for resumable uploads.
        // Parameters to MediaFileUpload are:
        // client, request, mimeType, data, resumable, chunksize.
        $media = new Google_Http_MediaFileUpload(
            $client,
            $request,
            $mimeType,
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(filesize($filePath));


        // Read the media file and upload it chunk by chunk.
        $status = false;
        $handle = fopen($filePath, "rb");
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        fclose($handle);
        return $status;
    }

    private
    function _mime_content_type($filename)
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

    private
    function system_extension_mime_types()
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

    private
    function addPropertyToResource(&$ref, $property, $value)
    {
        $keys = explode(".", $property);
        $is_array = false;
        foreach ($keys as $key) {
            // For properties that have array values, convert a name like
            // "snippet.tags[]" to snippet.tags, and set a flag to handle
            // the value as an array.
            if (substr($key, -2) == "[]") {
                $key = substr($key, 0, -2);
                $is_array = true;
            }
            $ref = &$ref[$key];
        }

        // Set the property value. Make sure array values are handled properly.
        if ($is_array && $value) {
            $ref = $value;
            $ref = explode(",", $value);
        } elseif ($is_array) {
            $ref = array();
        } else {
            $ref = $value;
        }
    }

// Build a resource based on a list of properties given as key-value pairs.
    private
    function createResource($properties)
    {
        $resource = array();
        foreach ($properties as $prop => $value) {
            if ($value) {
                $this->addPropertyToResource($resource, $prop, $value);
            }
        }
        return $resource;
    }

    public
    function deleteVideo(YoutubeAccount $youtubeAccount, $videoId)
    {
        $client = $this->clientAccountSettings($youtubeAccount);
        $youtube = new Google_Service_YouTube($client);
        try {
            $youtube->videos->delete($videoId);
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }

    public
    function getChannel($youtubeAccount)
    {
        $channels = null;
        $client = $this->clientAccountSettings($youtubeAccount);
        $youtube = new Google_Service_YouTube($client);
        try {
            foreach ($youtube->channels->listChannels("snippet, contentDetails", ['mine' => true]) as $key => $channel) {
                $channels[$channel->getSnippet()->getTitle()] = $channel->getId();
            }
            return $channels;
        } catch (Google_Service_Exception $e) {
            try {
                $this->refreshAccessToken($youtubeAccount);
                $this->getChannel($youtubeAccount);
            } catch (\Exception $e) {
                throw new OAuthCompanyException(json_encode($e->getMessage()));
            }
        }
    }

    public function removePost(YoutubePost $youtubePost)
    {
        $youtubeAccount = $youtubePost->getAccount();
        $client = $this->clientAccountSettings($youtubeAccount);
        $youtube = new Google_Service_YouTube($client);
        try {
            $youtube->videos->delete($youtubePost->getVideoId());
        } catch (\Exception $e) {
            throw new OAuthCompanyException($e->getMessage());
        }
    }
}