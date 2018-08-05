<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\truelocalAccount;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\interfaces\BaseInterface;
use SingAppBundle\Services\interfaces\CreateServiceAccountInterface;
use SingAppBundle\Services\interfaces\ScraperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class TruelocalService implements BaseInterface, ScraperInterface, CreateServiceAccountInterface
{
    private $em;
    private $curl;
    private $webDir;
    /**
     * @var BusinessInfo
     */
    private $business;
    private $message = 'truelocal service error, please connect to technical advice';
    private $url = 'https://api.truelocal.com.au/rest';
    private $urlLogin = 'https://api.truelocal.com.au/rest/auth/login?passToken=V0MxbDBlV2VNUw==';
    private $urlEditBusiness = 'https://www.truelocal.com/UpdateDetails.aspx?editSection=ContactDetails&CompanyID=';
    private $session;

    /**
     * truelocalService constructor.
     * @param EntityManagerInterface $entityManager
     * @param $webDir
     * @param Session $session
     */
    public function __construct(EntityManagerInterface $entityManager, $webDir, Session $session)
    {
        $this->em = $entityManager;
        $this->webDir = $webDir;
        $this->curl = $curl = new Curl();
        $this->session = $session;
    }

    /**
     * @param SocialNetworkAccount $truelocalAccount
     * @return mixed|null
     * @throws OAuthCompanyException
     */
    public function auth(SocialNetworkAccount $truelocalAccount)
    {
        $companyId = null;

        if ($truelocalAccount instanceof TruelocalAccount) {
            $this->curl->setHeaders(
                [
                    'content-type' =>  'application/json'
                ]
            );
            $params['email'] = $truelocalAccount->getUserEmail();
            $params['password'] = $truelocalAccount->getUserPassword();
            $this->curl->post($this->urlLogin, json_encode($params));
            if(isset($this->curl->response->meta) && $this->curl->response->meta->httpCode === 200){
                $token = $this->getToken($this->curl->response->data);
                $myProfileUrl = $this->getMyProfile($this->curl->response->data);
            }else{
                throw new OAuthCompanyException('Login or password incorrect');
            }
            $this->saveCookies('truelocal_' . $truelocalAccount->getUserEmail(),    ['token'=>$token]);
        }
        return $this->url.$myProfileUrl.'?passToken='.$token;
    }

    public function getProfileData($url, SocialNetworkAccount $truelocalAccount)
    {
        $response = null;
        $this->curl->get($url);
        if(isset($this->curl->response->meta) && $this->curl->response->meta->httpCode === 200) {
            $response = $this->curl->response->data->id;
        }else{
           $url = $this->auth($truelocalAccount);
           $this->getProfileData($url, $truelocalAccount);
        }
        return $response;
    }

    /**
     * @param SocialNetworkAccount $truelocalAccount
     * @param $companyId
     * @return SocialNetworkAccount
     */
    public function createAccount(SocialNetworkAccount $truelocalAccount, $profile)
    {
        $createdDate = new \DateTime();

        $truelocalAccount->setCreated($createdDate);
        $truelocalAccount->setProfile($profile);

        $this->em->persist($truelocalAccount);
        $this->em->flush();

        return $truelocalAccount;
    }

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @return null|object
     */

    public function getAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:TruelocalAccount');
        $truelocal = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $truelocal;
    }

    /**
     * @param SocialNetworkAccount $truelocalAccount
     * @param BusinessInfo $business
     * @throws OAuthCompanyException
     */
    public function editAccount(SocialNetworkAccount $truelocalAccount, BusinessInfo $business)
    {
        $this->business = $business;
        $fileName = $this->webDir . '/cookies/cookies_truelocal_' . $truelocalAccount->getUserEmail() . '.txt';
        if (file_exists($fileName) && $truelocalAccount instanceof truelocalAccount) {
            $cookies = json_decode(file_get_contents($fileName), true);
            $this->curl->setHeaders(['content-type' =>  'application/json']);
            $address = new \stdClass();
            $address->suburb = $business->getLocality();
            $address->postCode = $business->getPostalCode();
            $address->state = $business->getRegionCode();
            $params['firstName'] = $business->getName();
            $params['displayName'] = $business->getName();
            $params['address'] = $address;
            $params['description'] = $business->getDescription();
            $params['phoneNumber'] = trim($business->getPhoneNumber(), '+');
            $params['hideSuburb'] = false;
            $this->curl->post('https://api.truelocal.com.au/rest/users/'.$truelocalAccount->getProfile().'/update?passToken=' . 'xcdsfesd', json_encode($params));
            var_dump($this->curl->response); die;
            if ($this->curl->response->meta->httpCode === 400) {
                throw new OAuthCompanyException($this->curl->response->meta->errors);
            }

        } else {
            throw new OAuthCompanyException($this->message);
        }
    }


    private function getToken($content)
    {
        return $content->passToken;
    }

    private function getMyProfile($content)
    {
        return $content->myProfile;
    }

    private function getCompanyId($content, $str)
    {
        $businessId = null;
        if (strpos($content, $str)) {
            $first_step = explode($str, $content);
            $second_step = explode('">', $first_step[1]);
            $businessId = str_replace('"', '', $second_step[0]);
        }
        return $businessId;
    }

    public function saveCookies($prefix, $cookies)
    {
        $dir = 'cookies';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $cookiesFile = fopen($dir . '/cookies_' . $prefix . '.txt', 'w');
        fwrite($cookiesFile, json_encode($cookies));
        fclose($cookiesFile);
    }

    public function createServiceAccount($data, BusinessInfo $business)
    {
        $this->curl->setCookies(array_merge($this->session->get('coocie_truelocal'),['truelocalClickThrough' => 'OriginalReferrer=https://www.truelocal.com/AddYourBusinessSingle.aspx']));
        $params['__LASTFOCUS'] = '';
        $params['__EVENTTARGET'] = 'ctl00$contentSection$btnFinish';
        $params['__EVENTARGUMENT'] = '';
        $params['__VIEWSTATE'] = '/wEPDwULLTIxMTc0NjMzMjIPFgIeCFdvcmtmbG93MvcsAAEAAAD/////AQAAAAAAAAAMAgAAAEdIb3RGcm9nLkJ1c2luZXNzLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbAUBAAAAMEhvdEZyb2cuQnVzaW5lc3MuQWRkWW91ckJ1c2luZXNzLldvcmtmbG93TWFuYWdlcgUAAAAQX2J1c2luZXNzTGlzdGluZwpfc2Vzc2lvbklkEF9pc0FkZEFuZFVwZ3JhZGUTX0lzUmVnaXN0ZXJBbmRMb2dpbgpfcHJvbW9Db2RlBAEAAAEwSG90RnJvZy5CdXNpbmVzcy5BZGRZb3VyQnVzaW5lc3MuQnVzaW5lc3NMaXN0aW5nAgAAAAEBAgAAAAkDAAAABgQAAAAYcXpqenlhbXlocDR0azBqNGlnbGhka25nAAAKDAUAAABFSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsBQMAAAAwSG90RnJvZy5CdXNpbmVzcy5BZGRZb3VyQnVzaW5lc3MuQnVzaW5lc3NMaXN0aW5nDQAAABBfSXNBZGRBbmRVcGdyYWRlE19Jc1JlZ2lzdGVyQW5kTG9naW4IX3NlY3Rpb24QX2Z1cnRoZXN0U2VjdGlvbghfY29tcGFueQ1fYnVzaW5lc3NUeXBlCF9waHJhc2VzBV91c2VyEV91c2VyU3Vic2NyaXB0aW9uD191c2VyVHJhY2tpbmdJRBlfdXNlclRyYWNraW5nQWN0aXZpdHlUeXBlDF9xdWVyeVN0cmluZwpfcHJvbW9Db2RlAAAEBAQEAwQDAAQBAQEBLEhvdEZyb2cuQnVzaW5lc3MuQWRkWW91ckJ1c2luZXNzLlNlY3Rpb25UeXBlAgAAACxIb3RGcm9nLkJ1c2luZXNzLkFkZFlvdXJCdXNpbmVzcy5TZWN0aW9uVHlwZQIAAAAgSG90RnJvZy5Db21tb24uRFRPLkNvbXBhbnlFbnRpdHkFAAAAJUhvdEZyb2cuQ29tbW9uLkRUTy5CdXNpbmVzc1R5cGVFbnRpdHkFAAAAiwFTeXN0ZW0uQ29sbGVjdGlvbnMuR2VuZXJpYy5MaXN0YDFbW0hvdEZyb2cuQ29tbW9uLkRUTy5QaHJhc2VFbnRpdHksIEhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbF1dHUhvdEZyb2cuQ29tbW9uLkRUTy5Vc2VyRW50aXR5BQAAAI4BU3lzdGVtLkNvbGxlY3Rpb25zLkdlbmVyaWMuTGlzdGAxW1tIb3RGcm9nLkNvbW1vbi5EVE8uVXNlck9wdGluRW50aXR5LCBIb3RGcm9nLkNvbW1vbiwgVmVyc2lvbj0xLjAuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPW51bGxdXQg1SG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzTGlzdGluZ1VzZXJUcmFja2VyQWN0aXZpdHkFAAAAAgAAAAAABfr///8sSG90RnJvZy5CdXNpbmVzcy5BZGRZb3VyQnVzaW5lc3MuU2VjdGlvblR5cGUBAAAAB3ZhbHVlX18ACAIAAAAgAAAAAfn////6////AAAAAAkIAAAACQkAAAAJCgAAAAkLAAAACQwAAAAAAAAABfP///81SG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzTGlzdGluZ1VzZXJUcmFja2VyQWN0aXZpdHkBAAAAB3ZhbHVlX18ACAUAAAABAAAACgoFCAAAACBIb3RGcm9nLkNvbW1vbi5EVE8uQ29tcGFueUVudGl0eRwAAAAaPENvbXBhbnlJZD5rX19CYWNraW5nRmllbGQYPEFkZHJlc3M+a19fQmFja2luZ0ZpZWxkFjxQaG9uZT5rX19CYWNraW5nRmllbGQpPFBob25lVmFsaWRhdGlvbkRpc3BsYXllZD5rX19CYWNraW5nRmllbGQUPEZheD5rX19CYWNraW5nRmllbGQWPEVtYWlsPmtfX0JhY2tpbmdGaWVsZCk8RW1haWxWYWxpZGF0aW9uRGlzcGxheWVkPmtfX0JhY2tpbmdGaWVsZBg8V2Vic2l0ZT5rX19CYWNraW5nRmllbGQfPERpc3BsYXlXZWJzaXRlPmtfX0JhY2tpbmdGaWVsZCs8V2Vic2l0ZVZhbGlkYXRpb25EaXNwbGF5ZWQ+a19fQmFja2luZ0ZpZWxkHDxEZXNjcmlwdGlvbj5rX19CYWNraW5nRmllbGQiPElzVmFsaWRXZWJBZGRyZXNzPmtfX0JhY2tpbmdGaWVsZBw8T3duZXJzaGlwSWQ+a19fQmFja2luZ0ZpZWxkHjxJc1BhaWRMaXN0aW5nPmtfX0JhY2tpbmdGaWVsZCM8Q3VycmVudExpc3RpbmdUeXBlPmtfX0JhY2tpbmdGaWVsZBw8Q29tcGFueVJhbms+a19fQmFja2luZ0ZpZWxkHDxSZWxhdGVkVGFncz5rX19CYWNraW5nRmllbGQcPENvbXBhbnlMb2dvPmtfX0JhY2tpbmdGaWVsZBw8Q29tcGFueUd1aWQ+a19fQmFja2luZ0ZpZWxkIDxJc05lYXJieUNvbXBhbnk+a19fQmFja2luZ0ZpZWxkGzxSZWdpb25MaXN0PmtfX0JhY2tpbmdGaWVsZB08Tm9PZkVtcGxveWVlPmtfX0JhY2tpbmdGaWVsZCA8Tm9UZWxlbWFya2V0ZXJzPmtfX0JhY2tpbmdGaWVsZCpDb21wYW55QmFzZWAxKzxDb21wYW55TmFtZT5rX19CYWNraW5nRmllbGQxQ29tcGFueUJhc2VgMSs8VXJsU2FmZUNvbXBhbnlOYW1lPmtfX0JhY2tpbmdGaWVsZCtDb21wYW55QmFzZWAxKzxVcmxTYWZlU3RhdGU+a19fQmFja2luZ0ZpZWxkLENvbXBhbnlCYXNlYDErPFVybFNhZmVTdWJ1cmI+a19fQmFja2luZ0ZpZWxkHUNvbXBhbnlCYXNlYDErX3RoaXJkUGFydHlEYXRhAAQBAAEBAAEBAAEAAAAEAAEEAwABAAABAQEBBAggSG90RnJvZy5Db21tb24uRFRPLkFkZHJlc3NFbnRpdHkFAAAAAQEBAQgBJUhvdEZyb2cuQ29tbW9uLkRUTy5Db21wYW55TGlzdGluZ1R5cGUFAAAACCVIb3RGcm9nLkNvbW1vbi5EVE8uQ29tcGFueUltYWdlRW50aXR5BQAAAAtTeXN0ZW0uR3VpZAEIASxIb3RGcm9nLkNvbW1vbi5EVE8uVGhpcmRQYXJ0eS5UaGlyZFBhcnR5RGF0YQUAAAAFAAAAAAAAAAkOAAAABg8AAAAAAAkPAAAACQ8AAAAACQ8AAAAKAAkPAAAAAAAAAAAABfD///8lSG90RnJvZy5Db21tb24uRFRPLkNvbXBhbnlMaXN0aW5nVHlwZQEAAAAHdmFsdWVfXwAIBQAAAAEAAAAAAAAACgoE7////wtTeXN0ZW0uR3VpZAsAAAACX2ECX2ICX2MCX2QCX2UCX2YCX2cCX2gCX2kCX2oCX2sAAAAAAAAAAAAAAAgHBwICAgICAgICAAAAAAAAAAAAAAAAAAAAAAAJDwAAAAAAAAAACQ8AAAAJDwAAAAkPAAAACQ8AAAAKBQkAAAAlSG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzVHlwZUVudGl0eQkAAAAfPEJ1c2luZXNzVHlwZUlkPmtfX0JhY2tpbmdGaWVsZCU8UGFyZW50QnVzaW5lc3NUeXBlSWQ+a19fQmFja2luZ0ZpZWxkHDxMZXZlbE51bWJlcj5rX19CYWNraW5nRmllbGQhPEJ1c2luZXNzVHlwZU5hbWU+a19fQmFja2luZ0ZpZWxkJTxCdXNpbmVzc1R5cGVTdGF0dXNJZD5rX19CYWNraW5nRmllbGQYPFRvcGljSWQ+a19fQmFja2luZ0ZpZWxkJTxCdXNpbmVzc1R5cGVTb3VyY2VJZD5rX19CYWNraW5nRmllbGQWPElzTmV3PmtfX0JhY2tpbmdGaWVsZCM8Q2hpbGRCdXNpbmVzc1R5cGVzPmtfX0JhY2tpbmdGaWVsZAMDAAEAAAAAAwtTeXN0ZW0uR3VpZAtTeXN0ZW0uR3VpZAgICAgBkQFTeXN0ZW0uQ29sbGVjdGlvbnMuR2VuZXJpYy5MaXN0YDFbW0hvdEZyb2cuQ29tbW9uLkRUTy5CdXNpbmVzc1R5cGVFbnRpdHksIEhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbF1dBQAAAAHu////7////wAAAAAAAAAAAAAAAAAAAAAB7f///+////8AAAAAAAAAAAAAAAAAAAAAAAAAAAkPAAAAAAAAAAAAAAAAAAAAAAkUAAAABAoAAACLAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlBocmFzZUVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0DAAAABl9pdGVtcwVfc2l6ZQhfdmVyc2lvbgQAACFIb3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5W10FAAAACAgJFQAAAAAAAAAAAAAABQsAAAAdSG90RnJvZy5Db21tb24uRFRPLlVzZXJFbnRpdHkPAAAAFzxVc2VySWQ+a19fQmFja2luZ0ZpZWxkGTxVc2VybmFtZT5rX19CYWNraW5nRmllbGQZPFBhc3N3b3JkPmtfX0JhY2tpbmdGaWVsZBY8RW1haWw+a19fQmFja2luZ0ZpZWxkITxQYXNzd29yZFF1ZXN0aW9uPmtfX0JhY2tpbmdGaWVsZB88UGFzc3dvcmRBbnN3ZXI+a19fQmFja2luZ0ZpZWxkFjxUaXRsZT5rX19CYWNraW5nRmllbGQZPEpvYlRpdGxlPmtfX0JhY2tpbmdGaWVsZB48Sm9iVGl0bGVBZEhvYz5rX19CYWNraW5nRmllbGQVPFR5cGU+a19fQmFja2luZ0ZpZWxkHDxDb21wYW55TmFtZT5rX19CYWNraW5nRmllbGQaPEZpcnN0TmFtZT5rX19CYWNraW5nRmllbGQZPExhc3ROYW1lPmtfX0JhY2tpbmdGaWVsZBs8SXNWZXJpZmllZD5rX19CYWNraW5nRmllbGQbPE9wdGluVHlwZXM+a19fQmFja2luZ0ZpZWxkAwEBAQEBAQQBBAEBAQADC1N5c3RlbS5HdWlkIUhvdEZyb2cuQ29tbW9uLkRUTy5Kb2JUaXRsZUVudGl0eQUAAAAbSG90RnJvZy5Db21tb24uRFRPLlVzZXJUeXBlBQAAAAGOAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlVzZXJPcHRpbkVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0FAAAAAer////v////AAAAAAAAAAAAAAAAAAAAAAoKCgoKCgoKBen///8bSG90RnJvZy5Db21tb24uRFRPLlVzZXJUeXBlAQAAAAd2YWx1ZV9fAAgFAAAAAwAAAAoKCgAKBAwAAACOAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlVzZXJPcHRpbkVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0DAAAABl9pdGVtcwVfc2l6ZQhfdmVyc2lvbgQAACRIb3RGcm9nLkNvbW1vbi5EVE8uVXNlck9wdGluRW50aXR5W10FAAAACAgJGAAAAAIAAAACAAAABQ4AAAAgSG90RnJvZy5Db21tb24uRFRPLkFkZHJlc3NFbnRpdHkLAAAAG19hZGRyZXNzVmFsaWRhdGlvbkRpc3BsYXllZAlfYWRkcmVzczEJX2FkZHJlc3MyCV9hZGRyZXNzMwdfc3VidXJiBl9zdGF0ZQlfcG9zdGNvZGUIX2NvdW50cnkIX2dlb2NvZGUJX2Rpc3RhbmNlFl9sb2NhdGlvbkFjY3VyYWN5TGV2ZWwAAQEBAQEBAQQAAAEgSG90RnJvZy5Db21tb24uRFRPLkdlb2NvZGVFbnRpdHkFAAAABQgFAAAAAAoKCgoKBhkAAAAFODMyMTAKCgEwAAAAAAQUAAAAkQFTeXN0ZW0uQ29sbGVjdGlvbnMuR2VuZXJpYy5MaXN0YDFbW0hvdEZyb2cuQ29tbW9uLkRUTy5CdXNpbmVzc1R5cGVFbnRpdHksIEhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbF1dAwAAAAZfaXRlbXMFX3NpemUIX3ZlcnNpb24EAAAnSG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzVHlwZUVudGl0eVtdBQAAAAgICRoAAAAAAAAAAAAAAAcVAAAAAAEAAAAAAAAABB9Ib3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5BQAAAAcYAAAAAAEAAAAEAAAABCJIb3RGcm9nLkNvbW1vbi5EVE8uVXNlck9wdGluRW50aXR5BQAAAAkbAAAACRwAAAANAgcaAAAAAAEAAAAAAAAABCVIb3RGcm9nLkNvbW1vbi5EVE8uQnVzaW5lc3NUeXBlRW50aXR5BQAAAAUbAAAAIkhvdEZyb2cuQ29tbW9uLkRUTy5Vc2VyT3B0aW5FbnRpdHkCAAAAFjxPcHRpbj5rX19CYWNraW5nRmllbGQYPElzT3B0aW4+a19fQmFja2luZ0ZpZWxkBAAiSG90RnJvZy5Db21tb24uRFRPLk9wdGluVHlwZUVudGl0eQUAAAABBQAAAAkdAAAAAQEcAAAAGwAAAAkeAAAAAQUdAAAAIkhvdEZyb2cuQ29tbW9uLkRUTy5PcHRpblR5cGVFbnRpdHkDAAAAHDxPcHRpblR5cGVJZD5rX19CYWNraW5nRmllbGQfPFRyYW5zbGF0aW9uS2V5PmtfX0JhY2tpbmdGaWVsZB08RGVmYXVsdE9wdGluPmtfX0JhY2tpbmdGaWVsZAABAAgBBQAAAAEAAAAGHwAAABhzdWJzY3JpYmVUb0hvdEZyb2dPZmZlcnMBAR4AAAAdAAAAAgAAAAYgAAAAG3N1YnNjcmliZUhvdEZyb2dOZXdzbGV0dGVycwELFgJmD2QWCGYPZBYCAgMPFgIeB1Zpc2libGVoZAICEGRkFgYCAQ9kFgZmD2QWCGYPFgIfAWhkAgEPDxYEHgRUZXh0BQNIdWIeC05hdmlnYXRlVXJsBRZodHRwOi8vaG90ZnJvZy5jb20vc2JoZGQCAg8PFgQfAgUFTG9naW4fAwUiaHR0cHM6Ly93d3cuaG90ZnJvZy5jb20vTG9naW4uYXNweBYCHgV0aXRsZQUFTG9naW5kAgMPFgIfAWhkAgEPFgIfAWcWAmYPZBYCZg9kFgJmD2QWBAICDw8WBB4PVmFsaWRhdGlvbkdyb3VwBQxzZWFyY2hIZWFkZXIeDU9uQ2xpZW50Q2xpY2sFEGlzUG9zdGJhY2s9dHJ1ZTtkZAIDDw8WBB4MRXJyb3JNZXNzYWdlBSRZb3UgbWF5IGhhdmUgZm9yZ290dGVuIGEgc2VhcmNoIHRlcm0fBQUMc2VhcmNoSGVhZGVyZGQCAg8WAh8BZ2QCAg9kFhwCAg8WAh8CBS1CZSBmb3VuZCBieSBjdXN0b21lcnMgbG9va2luZyBmb3Igd2hhdCB5b3UgZG9kAgQPDxYCHwJlZGQCBg9kFgQCAQ9kFggCBQ9kFgRmDw8WAh8BaGRkAgMPDxYCHwFoZGQCBg9kFgRmDw8WAh8BaGRkAgMPDxYCHwFoZGQCBw9kFgRmDw8WAh8BaGRkAgMPDxYCHwFoZGQCCg9kFgICBA8PFgIeFFZhbGlkYXRpb25FeHByZXNzaW9uBRBeXGR7NX0oLVxkezR9KT8kZGQCAw9kFgICCA8PFgIfCAUsXHcrKFstKy4nXVx3KykqQFx3KyhbLS5dXHcrKSpcLlx3KyhbLS5dXHcrKSpkZAIHDxYCHwFoFgICAw8PFgIfBgUQaXNQb3N0YmFjaz10cnVlO2RkAggPFgIfAWhkAgoPZBYIZg9kFgICBg9kFggCAQ8QDxYCHgtfIURhdGFCb3VuZGcWAh4Ib25jaGFuZ2UF/QIgR2VuZXJhbEZvcm0uVmFsaWRhdGlvbi5kaXNwbGF5KCk7IFNldEpvYlRpdGxlQWRIb2NWaXNpYmlsaXR5X2N0bDAwX2NvbnRlbnRTZWN0aW9uX0xvZ2luUGFzc3dvcmRDb250cm9sX0pvYlRpdGxlX2RkbEpvYlRpdGxlKCk7IEdlbmVyYWxGb3JtLlZhbGlkYXRpb24uZGlzcGxheSgpOyBTZXRKb2JUaXRsZUFkSG9jVmlzaWJpbGl0eV9jdGwwMF9jb250ZW50U2VjdGlvbl9Mb2dpblBhc3N3b3JkQ29udHJvbF9Kb2JUaXRsZV9kZGxKb2JUaXRsZSgpOyBHZW5lcmFsRm9ybS5WYWxpZGF0aW9uLmRpc3BsYXkoKTsgU2V0Sm9iVGl0bGVBZEhvY1Zpc2liaWxpdHlfY3RsMDBfY29udGVudFNlY3Rpb25fTG9naW5QYXNzd29yZENvbnRyb2xfSm9iVGl0bGVfZGRsSm9iVGl0bGUoKTsQFQsNUGxlYXNlIHNlbGVjdCNCdXNpbmVzcyBvd25lciBvciBzZW5pb3IgbWFuYWdlbWVudAVTYWxlcwlNYXJrZXRpbmcPSHVtYW4gcmVzb3VyY2VzB0ZpbmFuY2UCSVQVT2ZmaWNlIGFkbWluaXN0cmF0aW9uElBlcnNvbmFsIGFzc2lzdGFudBRMZWdhbCByZXByZXNlbnRhdGl2ZQVPdGhlchULDVBsZWFzZSBzZWxlY3QaQnVzaW5lc3NPd25lclNlbmlvck1hbmFnZXIFU2FsZXMJTWFya2V0aW5nDkh1bWFuUmVzb3VyY2VzB0ZpbmFuY2UVSW5mb3JtYXRpb25UZWNobm9sb2d5C09mZmljZUFkbWluGlBlcnNvbmFsQXNzaXN0YW50U2VjcmV0YXJ5E0xlZ2FsUmVwcmVzZW50YXRpdmUFT3RoZXIUKwMLZ2dnZ2dnZ2dnZ2dkZAIDDw9kFgIfCgVmIEdlbmVyYWxGb3JtLlZhbGlkYXRpb24uZGlzcGxheSgpOyBHZW5lcmFsRm9ybS5WYWxpZGF0aW9uLmRpc3BsYXkoKTsgR2VuZXJhbEZvcm0uVmFsaWRhdGlvbi5kaXNwbGF5KCk7ZAIEDw8WAh8HBSRKdXN0IGluIGNhc2Ugd2UgbmVlZCB0byBjb250YWN0IHlvdS5kZAIFDw8WBB8HBSRKdXN0IGluIGNhc2Ugd2UgbmVlZCB0byBjb250YWN0IHlvdS4eDEluaXRpYWxWYWx1ZQUNUGxlYXNlIHNlbGVjdGRkAgQPDxYCHwgFLFx3KyhbLSsuJ11cdyspKkBcdysoWy0uXVx3KykqXC5cdysoWy0uXVx3KykqZGQCBg9kFgQCAQ8PZBYCHgV2YWx1ZQUHMTJhZG1pbmQCBg8PZBYCHwwFBzEyYWRtaW5kAgcPFgIfAWgWBAICDw8WAh4HRW5hYmxlZGhkZAIFDw8WAh8NaGRkAgsPFgIfAWhkAgwPFgIfAWhkAg0PDxYCHwIFGUNvbW11bmljYXRpb24gcHJlZmVyZW5jZXNkZAIODw8WAh8CBTJQbGVhc2Ugc2VsZWN0IHlvdXIgY29tbXVuaWNhdGlvbiBwcmVmZXJlbmNlcyBiZWxvd2RkAg8PZBYCAgEPZBYCZg8WAh4LXyFJdGVtQ291bnQCAhYEAgEPZBYCAgEPEA8WAh8CBawBSSB3b3VsZCBsaWtlIHRvIHJlY2VpdmUgbW9udGhseSByZXBvcnRzIG9uIHRoZSBwZXJmb3JtYW5jZSBvZiBteSBIb3Rmcm9nIHByb2ZpbGUgYXMgd2VsbCBhcyBuZXdzLCBpbmZvcm1hdGlvbiBhbmQgdGlwcyBvbiBob3cgdG8gaW1wcm92ZSBteSBwcm9maWxlIGFuZCBteSBvbmxpbmUgbWFya2V0aW5nLmRkZGQCAg9kFgICAQ8QDxYCHwIFkwFJIHdvdWxkIGxpa2UgdG8gcmVjZWl2ZSByZWxldmFudCBvZmZlcnMgYW5kIG9jY2FzaW9uYWwgaGVscGZ1bCB0aXBzIGZyb20gSG90ZnJvZyBwYXJ0bmVycyB2aWEgSG90ZnJvZyBEYXRhIFNlcnZpY2UsIGVNZWRpYSBhbmQgb3RoZXIgdGhpcmQtcGFydGllcy5kZGRkAhAPZBYCAgEPZBYCZg9kFgJmD2QWAgIBDw9kFgIeB29ua2V5dXAFJnRoaXMudmFsdWUgPSB0aGlzLnZhbHVlLnRvTG93ZXJDYXNlKCk7ZAIRDxYCHwIFkQJXaGVuIHlvdSBhZGQgeW91ciBwcm9maWxlIHRvIEhvdGZyb2csIHlvdSBhcmUgYWNjZXB0aW5nIG91ciA8YSBocmVmPSIvVGVybXMuYXNweCIgdGFyZ2V0PSJfYmxhbmsiPlRlcm1zIG9mIFVzZTwvYT4gYW5kIDxhIGhyZWY9Ii9Qcml2YWN5LmFzcHgiIHRhcmdldD0iX2JsYW5rIj5Qcml2YWN5IFBvbGljeTwvYT4uIFdlIHdpbGwgc2VuZCB5b3UgcmVwb3J0cywgdGlwcyBhbmQgbmV3cyB2aWEgZW1haWwuIENsaWNrIOKAmFN1Ym1pdOKAmSB0byBhZGQgeW91ciBwcm9maWxlIG5vdy5kAhIPZBYCAgEPDxYCHwYFEGlzUG9zdGJhY2s9dHJ1ZTtkZAIED2QWGmYPZBYCZg9kFgJmD2QWAmYPZBYEAgIPDxYEHwUFDHNlYXJjaEZvb3Rlch8GBRBpc1Bvc3RiYWNrPXRydWU7ZGQCAw8PFgQfBwUkWW91IG1heSBoYXZlIGZvcmdvdHRlbiBhIHNlYXJjaCB0ZXJtHwUFDHNlYXJjaEZvb3RlcmRkAgMPFgIfAWcWCAIBDxYEHgRocmVmBSJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9Ib3Rmcm9nVVNBHwQFFUZvbGxvdyB1cyBvbiBGYWNlYm9va2QCAg8WBB8QBSJodHRwczovL3R3aXR0ZXIuY29tLyMhL2hvdGZyb2dpbmZvHwQFFEZvbGxvdyB1cyBvbiBUd2l0dGVyZAIDDxYEHxAFSGh0dHA6Ly93d3cubGlua2VkaW4uY29tL2NvbXBhbnkvaG90ZnJvZy0tLXRoZS13b3JsZCdzLWJ1c2luZXNzLWRpcmVjdG9yeR8EBRVGb2xsb3cgdXMgb24gTGlua2VkSW5kAgQPFgQfEAUtaHR0cHM6Ly9wbHVzLmdvb2dsZS5jb20vMTEzNDgxODIwNDg4MTM0MTMwNDk3HwQFFEZvbGxvdyB1cyBvbiBHb29nbGUrZAIEDxYCHwFnFgJmDxYEHxAFDS9BYm91dFVTLmFzcHgeA3JlbAUIbm9mb2xsb3dkAgUPFgIfAWcWAmYPFgQfEAUWL0hvdGZyb2dQcm9tb3Rpb24uYXNweB8RZWQCBg8WAh8BZxYCZg8WBB8QBRkvQWR2ZXJ0aXNpbmdQYXJ0bmVycy5hc3B4HxFlZAIIDxYCHwFnFgJmDxYEHxAFCy9UZXJtcy5hc3B4HxEFCG5vZm9sbG93ZAIJDxYCHwFnFgJmDxYEHxAFDS9Qcml2YWN5LmFzcHgfEQUIbm9mb2xsb3dkAgwPFgIfAWcWAmYPFgYfEAU5aHR0cDovL3N1cHBvcnQuaG90ZnJvZy5jb20vY3VzdG9tZXIvZW5fdXMvcG9ydGFsL2FydGljbGVzHxEFCG5vZm9sbG93HgZ0YXJnZXQFBl9ibGFua2QCEA8WBh8QBRZodHRwOi8vaG90ZnJvZy5jb20vc2JoHxFlHwFnZAIRDxYCHwFnFgJmDxYEHxAFM2h0dHA6Ly9ob3Rmcm9nLmNvbS9zYmgvY2F0ZWdvcnkvZ3Jvdy15b3VyLWJ1c2luZXNzLx8RZWQCEg8WAh8BZxYCZg8WBB8QBSpodHRwOi8vaG90ZnJvZy5jb20vc2JoL2NhdGVnb3J5L21hcmtldGluZy8fEWVkAhMPFgIfAWcWAmYPFgQfEAVUaHR0cDovL3N1cHBvcnQuaG90ZnJvZy5jb20vY3VzdG9tZXIvZW5fdXMvcG9ydGFsL3RvcGljcy84MDU1NTEtaG90ZnJvZy10aXBzL2FydGljbGVzHxFlZAIVDw8WAh8CBQ8xNy4wLjAuMDAxIC0gNjRkZAIEDw8WAh8BaGRkAgUPDxYCHwFoZGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgMFPWN0bDAwJGNvbnRlbnRTZWN0aW9uJENvbnRhY3REZXRhaWxzQ29udHJvbCRjaGJOb1RlbGVtYXJrZXRlcnMFSWN0bDAwJGNvbnRlbnRTZWN0aW9uJFVzZXJTdWJzcmlwdGlvbiRzdWJzY3JpcHRpb25MaXN0JGN0bDAxJG9wdGluQ2hlY2tCb3gFSWN0bDAwJGNvbnRlbnRTZWN0aW9uJFVzZXJTdWJzcmlwdGlvbiRzdWJzY3JpcHRpb25MaXN0JGN0bDAyJG9wdGluQ2hlY2tCb3hYSU5BZ8tP9yctcgFLsAA4bc0+Bw==';
        $params['__VIEWSTATEGENERATOR'] = ' B424CFAF';
        $params['ctl00$truelocalHeader$truelocalSearch$txtWhat'] = '';
        $params['ctl00$truelocalHeader$truelocalSearch$txtWhere'] = '';
        $params['ctl00$contentSection$CompanyDetailsControl$txtBusinessName'] = $business->getName();
        $params['ctl00$contentSection$CompanyDetailsControl$txtStreetAddress'] = $business->getAddress();
        $params['ctl00$contentSection$CompanyDetailsControl$txtAddress2'] = '';
        $params['ctl00$contentSection$CompanyDetailsControl$txtAddress3'] = '';
        $params['ctl00$contentSection$CompanyDetailsControl$txtSuburb'] = 'Aberdeen';
        $params['ctl00$contentSection$CompanyDetailsControl$cboState'] = 'ID';
        $params['ctl00$contentSection$CompanyDetailsControl$txtPostcode'] = '83210';
        $params['ctl00$contentSection$ContactDetailsControl$txtPhone'] = $business->getPhoneNumber();
        $params['ctl00$contentSection$ContactDetailsControl$txtEmail'] = $business->getEmail();
        $params['ctl00$contentSection$ContactDetailsControl$txtWebsite'] = $business->getWebsite();
        $params['ctl00$contentSection$ContactDetailsControl$txtDescription'] = $business->getDescription();
        $params['ctl00$contentSection$ContactDetailsControl$txtKeywords'] = '';
        $params['ctl00$contentSection$ContactDetailsControl$KeywordsCsv'] = 'new';
        $params['ctl00$contentSection$LoginPasswordControl$txtFirstName'] = $data['truelocal']['firstName'];
        $params['ctl00$contentSection$LoginPasswordControl$txtLastName'] = $data['truelocal']['Surname'];
        $params['ctl00$contentSection$LoginPasswordControl$JobTitle$ddlJobTitle'] = 'BusinessOwnerSeniorManager';
        $params['ctl00$contentSection$LoginPasswordControl$JobTitle$txtJobTitleAdHoc'] = 'other';
        $params['ctl00$contentSection$LoginPasswordControl$txtAdminEmail'] = $data['truelocal']['email'];
        $params['ctl00$contentSection$LoginPasswordControl$txtPassword'] = $data['truelocal']['userPassword'];
        $params['ctl00$contentSection$LoginPasswordControl$txtConfirmPassword'] = $data['truelocal']['userPassword'];
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl01$optinCheckBox'] = 'on';
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl01$optinTypeHidden'] = 1;
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl01$optinTypeTranslationKey'] = 'subscribeTotruelocalOffers';
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl02$optinCheckBox'] = 'on';
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl02$optinTypeHidden'] = 2;
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl02$optinTypeTranslationKey'] = 'subscribetruelocalNewsletters';
        $params['LBD_VCT_addyourbusinesssingle_ctl00_contentsection_captchamanager_ctl00_captcha'] = $data['truelocal']['captcha-hash'];
        $params['ctl00$contentSection$CaptchaManager$ctl00$txtCaptcha'] = strtolower($data['truelocal']['captcha']);
        $params['ctl00$truelocalFooter$truelocalSearch$txtWhat'] = '';
        $params['ctl00$truelocalFooter$truelocalSearch$txtWhere'] = '';
        $params['ctl00$HiddenSocialUID'] = '';
        $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 0;
        if ($this->curl->httpStatusCode !== 302) {
            throw new OAuthCompanyException($this->message);
        }
        $this->curl->post('https://www.truelocal.com/AddYourBusinessSingle.aspx', $params);
        print($this->curl->response); die;
    }

    public function getCaptcha()
    {
        $this->curl->get('https://www.truelocal.com/AddYourBusinessSingle.aspx');
        $this->session->set('coocie_truelocal', $this->curl->getResponseCookies());
        $dom = new DomDocument();
        $dom->loadHTMLFile('https://www.truelocal.com/AddYourBusinessSingle.aspx');
        $captchaImage = 'https://www.truelocal.com/'.$dom->getElementById('addyourbusinesssingle_ctl00_contentsection_captchamanager_ctl00_captcha_CaptchaImage')->getAttribute('src');
        $captchaText = $dom->getElementById('LBD_VCT_addyourbusinesssingle_ctl00_contentsection_captchamanager_ctl00_captcha')->getAttribute('value');
        return [$captchaImage, $captchaText];
    }
}