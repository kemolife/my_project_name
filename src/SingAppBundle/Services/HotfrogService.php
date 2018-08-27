<?php


namespace SingAppBundle\Services;


use Curl\Curl;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Google_Client;
use Serps\Core\Browser\Browser;
use Serps\Core\Http\StackingHttpClient;
use Serps\SearchEngine\Google\GoogleClient;
use Serps\SearchEngine\Google\GoogleUrl;
use SingAppBundle\Entity\BusinessInfo;
use SingAppBundle\Entity\HotfrogAccount;
use SingAppBundle\Entity\SocialNetworkAccount;
use SingAppBundle\Entity\User;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use SingAppBundle\Services\interfaces\BaseInterface;
use SingAppBundle\Services\interfaces\CreateServiceAccountInterface;
use SingAppBundle\Services\interfaces\ScraperInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Serps\HttpClient\CurlClient;

class HotfrogService implements BaseInterface, ScraperInterface, CreateServiceAccountInterface
{
    use GoogleSearchTrait;

    const NAME_FOR_SEARCH = 'hotfrog.com';

    private $em;
    private $curl;
    private $webDir;
    /**
     * @var BusinessInfo
     */
    private $business;
    private $message = 'hotfrog service error, please connect to technical advice';
    private $urlLogin = 'https://www.hotfrog.com/Login.aspx';
    private $urlEditBusiness = 'https://www.hotfrog.com/UpdateDetails.aspx?editSection=ContactDetails&CompanyID=';
    private $session;

    /**
     * HotfrogService constructor.
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
     * @param SocialNetworkAccount $hotfrogAccount
     * @return mixed|null
     * @throws OAuthCompanyException
     */
    public function auth(SocialNetworkAccount $hotfrogAccount)
    {
        $companyId = null;

        if ($hotfrogAccount instanceof HotfrogAccount) {
            $params['__LASTFOCUS'] = '';
            $params['__EVENTTARGET'] = 'ctl00$contentSection$LoginButton';
            $params['__EVENTARGUMENT'] = '';
            $params['ctl00$hotFrogHeader$hotfrogSearch$txtWhere'] = '';
            $params['ctl00$contentSection$EmailAddress'] = $hotfrogAccount->getUserEmail();
            $params['ctl00$contentSection$Password'] = $hotfrogAccount->getUserPassword();
            $params['ctl00$hotFrogFooter$hotfrogSearch$txtWhat'] = '';
            $params['ctl00$hotFrogFooter$hotfrogSearch$txtWhere'] = '';
            $params['ctl00$HiddenSocialUID'] = '';
            $this->curl->post($this->urlLogin, $params);
            $companyId = $this->getCompanyId($this->curl->response, 'CompanyID=');
            if (null === $companyId) {
                throw new OAuthCompanyException('Login or password incorrect');
            }
            $this->saveCookies('hotfrog_' . $hotfrogAccount->getUserEmail(), $this->curl->getResponseCookies());
        }
        return $companyId;
    }

    /**
     * @param SocialNetworkAccount $hotfrogAccount
     * @param $companyId
     * @return SocialNetworkAccount
     */
    public function createAccount(SocialNetworkAccount $hotfrogAccount)
    {
        $createdDate = new \DateTime();

        $hotfrogAccount->setCreated($createdDate);

        $this->em->persist($hotfrogAccount);
        $this->em->flush();

        return $hotfrogAccount;
    }

    /**
     * @param User $user
     * @param BusinessInfo $business
     * @return null|object
     */

    public function getAccount(User $user, BusinessInfo $business)
    {
        $repository = $this->em->getRepository('SingAppBundle:HotfrogAccount');
        $hotfrog = $repository->findOneBy(['user' => $user, 'business' => $business]);

        return $hotfrog;
    }

    /**
     * @param SocialNetworkAccount $hotfrogAccount
     * @param BusinessInfo $business
     * @throws OAuthCompanyException
     */
    public function editAccount(SocialNetworkAccount $hotfrogAccount, BusinessInfo $business, $notLoop = false)
    {
        $this->business = $business;
        $fileName = $this->webDir . '/cookies/cookies_hotfrog_' . $hotfrogAccount->getUserEmail() . '.txt';
        if (file_exists($fileName) && $hotfrogAccount instanceof HotfrogAccount) {
            $cookies = json_decode(file_get_contents($fileName), true);
            $this->curl->setHeaders([
                'accept' => 'https://www.hotfrog.com',
                'referer' => 'https://www.hotfrog.com/UpdateDetails.aspx?editSection=ContactDetails&CompanyID='.$hotfrogAccount->getBusinessId(),
            ]);
            $params['__LASTFOCUS'] = '';
            $params['__EVENTTARGET'] = 'ctl00$contentSection$btnUpdate';
            $params['__EVENTARGUMENT'] = '';
            $params['__VIEWSTATE'] = '/wEPDwULLTE3OTUxMTQ5NDYPFgIeCEJVU0lORVNTMu4XAAEAAAD/////AQAAAAAAAAAMAgAAAEdIb3RGcm9nLkJ1c2luZXNzLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbAwDAAAARUhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbAUBAAAAM0hvdEZyb2cuQnVzaW5lc3MuVXBkYXRlWW91ckJ1c2luZXNzLkJ1c2luZXNzTGlzdGluZwMAAAAIX3NlY3Rpb24YPENvbXBhbnk+a19fQmFja2luZ0ZpZWxkFTxUYWdzPmtfX0JhY2tpbmdGaWVsZAQEAytIb3RGcm9nLkNvbW1vbi5EVE8uVXBkYXRlRGV0YWlsc1NlY3Rpb25UeXBlAwAAACBIb3RGcm9nLkNvbW1vbi5EVE8uQ29tcGFueUVudGl0eQMAAACLAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlBocmFzZUVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0CAAAABfz///8rSG90RnJvZy5Db21tb24uRFRPLlVwZGF0ZURldGFpbHNTZWN0aW9uVHlwZQEAAAAHdmFsdWVfXwAIAwAAAAEAAAAJBQAAAAkGAAAABQUAAAAgSG90RnJvZy5Db21tb24uRFRPLkNvbXBhbnlFbnRpdHkcAAAAGjxDb21wYW55SWQ+a19fQmFja2luZ0ZpZWxkGDxBZGRyZXNzPmtfX0JhY2tpbmdGaWVsZBY8UGhvbmU+a19fQmFja2luZ0ZpZWxkKTxQaG9uZVZhbGlkYXRpb25EaXNwbGF5ZWQ+a19fQmFja2luZ0ZpZWxkFDxGYXg+a19fQmFja2luZ0ZpZWxkFjxFbWFpbD5rX19CYWNraW5nRmllbGQpPEVtYWlsVmFsaWRhdGlvbkRpc3BsYXllZD5rX19CYWNraW5nRmllbGQYPFdlYnNpdGU+a19fQmFja2luZ0ZpZWxkHzxEaXNwbGF5V2Vic2l0ZT5rX19CYWNraW5nRmllbGQrPFdlYnNpdGVWYWxpZGF0aW9uRGlzcGxheWVkPmtfX0JhY2tpbmdGaWVsZBw8RGVzY3JpcHRpb24+a19fQmFja2luZ0ZpZWxkIjxJc1ZhbGlkV2ViQWRkcmVzcz5rX19CYWNraW5nRmllbGQcPE93bmVyc2hpcElkPmtfX0JhY2tpbmdGaWVsZB48SXNQYWlkTGlzdGluZz5rX19CYWNraW5nRmllbGQjPEN1cnJlbnRMaXN0aW5nVHlwZT5rX19CYWNraW5nRmllbGQcPENvbXBhbnlSYW5rPmtfX0JhY2tpbmdGaWVsZBw8UmVsYXRlZFRhZ3M+a19fQmFja2luZ0ZpZWxkHDxDb21wYW55TG9nbz5rX19CYWNraW5nRmllbGQcPENvbXBhbnlHdWlkPmtfX0JhY2tpbmdGaWVsZCA8SXNOZWFyYnlDb21wYW55PmtfX0JhY2tpbmdGaWVsZBs8UmVnaW9uTGlzdD5rX19CYWNraW5nRmllbGQdPE5vT2ZFbXBsb3llZT5rX19CYWNraW5nRmllbGQgPE5vVGVsZW1hcmtldGVycz5rX19CYWNraW5nRmllbGQqQ29tcGFueUJhc2VgMSs8Q29tcGFueU5hbWU+a19fQmFja2luZ0ZpZWxkMUNvbXBhbnlCYXNlYDErPFVybFNhZmVDb21wYW55TmFtZT5rX19CYWNraW5nRmllbGQrQ29tcGFueUJhc2VgMSs8VXJsU2FmZVN0YXRlPmtfX0JhY2tpbmdGaWVsZCxDb21wYW55QmFzZWAxKzxVcmxTYWZlU3VidXJiPmtfX0JhY2tpbmdGaWVsZB1Db21wYW55QmFzZWAxK190aGlyZFBhcnR5RGF0YQAEAQABAQABAQABAAAABAABBAMAAQAAAQEBAQQIIEhvdEZyb2cuQ29tbW9uLkRUTy5BZGRyZXNzRW50aXR5AwAAAAEBAQEIASVIb3RGcm9nLkNvbW1vbi5EVE8uQ29tcGFueUxpc3RpbmdUeXBlAwAAAAglSG90RnJvZy5Db21tb24uRFRPLkNvbXBhbnlJbWFnZUVudGl0eQMAAAALU3lzdGVtLkd1aWQBCAEsSG90RnJvZy5Db21tb24uRFRPLlRoaXJkUGFydHkuVGhpcmRQYXJ0eURhdGEDAAAAAwAAAKjnlgIJBwAAAAYIAAAADiszODA5Njk2OTAzNTMxAAYJAAAAAAYKAAAAFmtlbW9saWZlMTk5MEBnbWFpbC5jb20ABgsAAAASaHR0cDovL3Rlc3QuY29tLnVhCQkAAAAABg0AAAAEdGVzdAEAAAAAAAXy////JUhvdEZyb2cuQ29tbW9uLkRUTy5Db21wYW55TGlzdGluZ1R5cGUBAAAAB3ZhbHVlX18ACAMAAAABAAAAAAAAAAoKBPH///8LU3lzdGVtLkd1aWQLAAAAAl9hAl9iAl9jAl9kAl9lAl9mAl9nAl9oAl9pAl9qAl9rAAAAAAAAAAAAAAAIBwcCAgICAgICApjwJVJQItVPue4fHvoQHMcACQkAAAAAAAAAAAYRAAAADXRlc3RfYnVzaW5lc3MGEgAAABZ0ZXN0LWJ1c2luZXNzXzQzNDQ0MTM2CQkAAAAJCQAAAAoEBgAAAIsBU3lzdGVtLkNvbGxlY3Rpb25zLkdlbmVyaWMuTGlzdGAxW1tIb3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5LCBIb3RGcm9nLkNvbW1vbiwgVmVyc2lvbj0xLjAuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPW51bGxdXQMAAAAGX2l0ZW1zBV9zaXplCF92ZXJzaW9uBAAAIUhvdEZyb2cuQ29tbW9uLkRUTy5QaHJhc2VFbnRpdHlbXQMAAAAICAkUAAAAAQAAAAEAAAAFBwAAACBIb3RGcm9nLkNvbW1vbi5EVE8uQWRkcmVzc0VudGl0eQsAAAAbX2FkZHJlc3NWYWxpZGF0aW9uRGlzcGxheWVkCV9hZGRyZXNzMQlfYWRkcmVzczIJX2FkZHJlc3MzB19zdWJ1cmIGX3N0YXRlCV9wb3N0Y29kZQhfY291bnRyeQhfZ2VvY29kZQlfZGlzdGFuY2UWX2xvY2F0aW9uQWNjdXJhY3lMZXZlbAABAQEBAQEBBAAAASBIb3RGcm9nLkNvbW1vbi5EVE8uR2VvY29kZUVudGl0eQMAAAAFCAMAAAAABhUAAAAOTHbRlnYtcG9zaHRhbXQJCQAAAAkJAAAABhcAAAAJQWJiZXZpbGxlBhgAAAACQUwGGQAAAAUzNjMxMAoJGgAAAAEwBAAAAAcUAAAAAAEAAAAEAAAABB9Ib3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5AwAAAAkbAAAADQMFGgAAACBIb3RGcm9nLkNvbW1vbi5EVE8uR2VvY29kZUVudGl0eQIAAAACX3gCX3kAAAYGAwAAAH/AAwMIUFXASrVPx2OSP0AFGwAAAB9Ib3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5BwAAAAlfcGhyYXNlSUQLX3BocmFzZVN0ZW0LX3BocmFzZU5hbWUMX3VybFNhZmVOYW1lC19waHJhc2VUeXBlDV9jb21wYW55Q291bnQLX2lzRW5oYW5jZWQAAQEBBAAACB1Ib3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlVHlwZQMAAAAIAQMAAAAf3AIACgYcAAAABFRlc3QGHQAAAARUZXN0BeL///8dSG90RnJvZy5Db21tb24uRFRPLlBocmFzZVR5cGUBAAAAB3ZhbHVlX18ACAMAAAABAAAAXhIAAAALFgJmD2QWAgIDDxYCHgVjbGFzcwUOY29tcGFueWRldGFpbHMWCgIBEGRkFhhmD2QWBGYPZBYIZg9kFgZmD2QWAgIBDw8WBB4EVGV4dAUNdGVzdF9idXNpbmVzcx4LTmF2aWdhdGVVcmwFQGh0dHBzOi8vd3d3LmhvdGZyb2cuY29tL1dlbGNvbWVUb0hvdGZyb2cuYXNweD9Db21wYW55SUQ9NDM0NDQxMzZkZAIBDxYCHgdWaXNpYmxlaGQCAg9kFgJmDw8WBB8CBQdBY2NvdW50HwMFOWh0dHBzOi8vd3d3LmhvdGZyb2cuY29tL215YWNjb3VudC5hc3B4P0NvbXBhbnlJRD00MzQ0NDEzNmRkAgIPDxYEHwIFA0h1Yh8DBRZodHRwOi8vaG90ZnJvZy5jb20vc2JoZGQCAw8PFgQfAgUGTG9nb3V0HwMFI2h0dHBzOi8vd3d3LmhvdGZyb2cuY29tL0xvZ291dC5hc3B4FgIeB29uY2xpY2sFCWxvZ291dCgpO2QCBA8WAh8EaGQCAQ8WAh8EZxYIAgEPFgQeBGhyZWYFImh0dHA6Ly93d3cuZmFjZWJvb2suY29tL0hvdGZyb2dVU0EeBXRpdGxlBRVGb2xsb3cgdXMgb24gRmFjZWJvb2tkAgIPFgQfBgUiaHR0cHM6Ly90d2l0dGVyLmNvbS8jIS9ob3Rmcm9naW5mbx8HBRRGb2xsb3cgdXMgb24gVHdpdHRlcmQCAw8WBB8GBUhodHRwOi8vd3d3LmxpbmtlZGluLmNvbS9jb21wYW55L2hvdGZyb2ctLS10aGUtd29ybGQncy1idXNpbmVzcy1kaXJlY3RvcnkfBwUVRm9sbG93IHVzIG9uIExpbmtlZEluZAIEDxYEHwYFLWh0dHBzOi8vcGx1cy5nb29nbGUuY29tLzExMzQ4MTgyMDQ4ODEzNDEzMDQ5Nx8HBRRGb2xsb3cgdXMgb24gR29vZ2xlK2QCAQ8WAh8EaGQCAw9kFgJmDw8WAh8EaGRkAgQPFgIfBGhkAgUPDxYCHwRnZGQCBw9kFgICAQ8WAh8CBRBCdXNpbmVzcyBkZXRhaWxzZAIID2QWCAIDDxYCHwRoZAIHDxYCHwJkZAIJD2QWCAIBD2QWAgIBD2QWIAICDxYCHwIFKEx20ZZ2LXBvc2h0YW10LCBBYmJldmlsbGUsIEFMLCAzNjMxMCwgVVNkAgMPFgIfAgUcQWRkcmVzcyBjb3VsZCBub3QgYmUgbG9jYXRlZGQCBA8WAh8CBW1VbmZvcnR1bmF0ZWx5IHdlIHdlcmUgdW5hYmxlIHRvIG1hcCB5b3VyIGFkZHJlc3MuIFBsZWFzZSBjb25zaWRlciBtb2RpZnlpbmcgeW91ciBhZGRyZXNzIGRldGFpbHMgb24gdGhpcyBwYWdlZAIFDw8WAh8DBRNqYXZhc2NyaXB0OnZvaWQoMCk7FgIfBQWoAmlzUG9zdGJhY2s9dHJ1ZTsgd2luZG93SGFuZGxlID0gd2luZG93Lm9wZW4oJ2h0dHBzOi8vd3d3LmhvdGZyb2cuY29tL0luZm8uYXNweD9JbmZvPUNvdWxkTm90TWFwQWRkcmVzcycsICdob3Rmcm9naW5mbycsICdzdGF0dXM9MCx0b29sYmFyPTAsbG9jYXRpb249MCxtZW51YmFyPTAsZGlyZWN0b3JpZXM9MCxyZXNpemVhYmxlPTEsc2Nyb2xsYmFycz0xLHdpZHRoPTYwMCxoZWlnaHQ9MzAwJyk7IHdpbmRvd0hhbmRsZS5tb3ZlVG8oc2NyZWVuLndpZHRoIC8gMiAtIDMwMCwgMjAwKTsgd2luZG93SGFuZGxlLmZvY3VzKCk7ZAIPD2QWBAIBDw8WAh8EaGRkAgMPDxYCHwRoZGQCEA9kFgQCAQ8PFgIfBGhkZAIDDw8WAh8EaGRkAhEPZBYEAgEPDxYCHwRoZGQCAw8PFgIfBGhkZAIVD2QWAgIEDw8WAh4UVmFsaWRhdGlvbkV4cHJlc3Npb24FEF5cZHs1fSgtXGR7NH0pPyRkZAIZDw8WAh8EaGRkAh4PDxYCHwRoZGQCIA8PFgIfAgUSaHR0cDovL3Rlc3QuY29tLnVhZGQCIQ8PFgIfCAX4AV4oaHR0cHM/Oi8vKT8oKFswLTlhLXpBLVpfIX4qJygpLiY9KyQlLV0rOiApP1swLTlhLXpBLVpfIX4qJygpLiY9KyQlLV0rQCk/KChbMC05XXsxLDN9XC4pezN9WzAtOV17MSwzfXwoWzAtOWEtekEtWl8hfionKCktXStcLikqKFswLTlhLXpBLVpdWzAtOWEtekEtWi1dezAsNjF9KT9bMC05YS16QS1aXVwuW2EtekEtWl17MiwxNH0pKDpbMC05XXsxLDR9KT8oKC8/KXwoL1swLTlhLXpBLVpfIX4qJygpLjs/OkAmPSskLCUjLV0rKSsvPykkZGQCJQ8PFgIfAgUWa2Vtb2xpZmUxOTkwQGdtYWlsLmNvbWRkAicPDxYCHwgFLFx3KyhbLSsuJ11cdyspKkBcdysoWy0uXVx3KykqXC5cdysoWy0uXVx3KykqZGQCKQ8PFgIfAwXKAWh0dHBzOi8vd3d3LmhvdGZyb2cuY29tL1VwZGF0ZURldGFpbHMuYXNweD9lZGl0U2VjdGlvbj1QYXltZW50VHlwZXMmQ29tcGFueUlEPTQzNDQ0MTM2JnJlZGlyZWN0VVJMPWh0dHAlM2ElMmYlMmZ3d3cuaG90ZnJvZy5jb20lMmZVcGRhdGVEZXRhaWxzLmFzcHglM2ZlZGl0U2VjdGlvbiUzZENvbnRhY3REZXRhaWxzJTI2Q29tcGFueUlEJTNkNDM0NDQxMzZkZAIqDw8WAh8DBcoBaHR0cHM6Ly93d3cuaG90ZnJvZy5jb20vVXBkYXRlRGV0YWlscy5hc3B4P2VkaXRTZWN0aW9uPVRyYWRpbmdIb3VycyZDb21wYW55SUQ9NDM0NDQxMzYmcmVkaXJlY3RVUkw9aHR0cCUzYSUyZiUyZnd3dy5ob3Rmcm9nLmNvbSUyZlVwZGF0ZURldGFpbHMuYXNweCUzZmVkaXRTZWN0aW9uJTNkQ29udGFjdERldGFpbHMlMjZDb21wYW55SUQlM2Q0MzQ0NDEzNmRkAgIPZBYCAgEPZBYCAgQPFgIfBwVlSG90ZnJvZyBhdXRvbWF0aWNhbGx5IHVzZXMgeW91ciBhZGRyZXNzIGRldGFpbHMgdG8gaGlnaGxpZ2h0IHlvdSBpbiBjaXR5IGFuZCBzdGF0ZSBsb2NhdGlvbiBzZWFyY2hlcy5kAgMPZBYCAgEPZBYCZg8QZGQWAGQCBg9kFgICAQ9kFgICAw8QZGQWAGQCCw9kFgICAQ8PFgIeDU9uQ2xpZW50Q2xpY2sFEGlzUG9zdGJhY2s9dHJ1ZTtkZAIKD2QWAmYPFgIfBGgWEAIBDxYCHwIFIUltcHJvdmUgeW91ciBIb3Rmcm9nIEFkVmFudGFnZSBhZGQCAw9kFgJmDw8WAh8CBRhBZGQgeW91ciBsb2dvIHRvIHlvdXIgYWRkZAIED2QWAmYPDxYCHwIFEEFkZCB5b3VyIHdlYnNpdGVkZAIFD2QWAgIBDw8WAh8CBRNHZXQgYSBmcmVlIHdlYnNpdGUgZGQCBg9kFgICAQ8PFgIfAgUkU2lnbnVwIG5vdyB0byBleHRlbmQgeW91ciBmcmVlIHRyaWFsZGQCBw9kFgJmDw8WAh8CBRJSZXZpZXcgeW91ciBhZCBub3dkZAIID2QWAmYPDxYCHwIFEVJlZmVyIHRvIGJ1c2luZXNzZGQCCQ9kFgICAQ8PFgIfAgUSUmV2aWV3IHlvdXIgYWQgbm93ZGQCCw9kFhRmD2QWAgIGDw8WAh8CBR1Zb3VyIFByb2ZpbGUgaXMgNDAlIGNvbXBsZXRlLmRkAgIPFgIfAgU4V2luIG5ldyBjdXN0b21lcnMgd2l0aCBhIGNvbXBsZXRlIGFuZCB1cC10by1kYXRlIHByb2ZpbGVkAgQPDxYCHwJkZGQCBg8WAh8CBQI0MGQCCA8WAh8CBUJZb3VyIHByb2ZpbGUgaXMgPHNwYW4gY2xhc3M9InBlcmNlbnQtY29tcGxldGUiPjQwJTwvc3Bhbj4gY29tcGxldGVkAgoPDxYCHwMFE2phdmFzY3JpcHQ6dm9pZCgwKTsWAh8FBaQCaXNQb3N0YmFjaz10cnVlOyB3aW5kb3dIYW5kbGUgPSB3aW5kb3cub3BlbignaHR0cHM6Ly93d3cuaG90ZnJvZy5jb20vSW5mby5hc3B4P0luZm89SW1wcm92ZVByb2ZpbGUnLCAnaG90ZnJvZ2luZm8nLCAnc3RhdHVzPTAsdG9vbGJhcj0wLGxvY2F0aW9uPTAsbWVudWJhcj0wLGRpcmVjdG9yaWVzPTAscmVzaXplYWJsZT0xLHNjcm9sbGJhcnM9MSx3aWR0aD02MDAsaGVpZ2h0PTMwMCcpOyB3aW5kb3dIYW5kbGUubW92ZVRvKHNjcmVlbi53aWR0aCAvIDIgLSAzMDAsIDIwMCk7IHdpbmRvd0hhbmRsZS5mb2N1cygpO2QCCw8PFgIfAwU3aHR0cHM6Ly93d3cuaG90ZnJvZy5jb20vYnVzaW5lc3MvdGVzdC1idXNpbmVzc180MzQ0NDEzNmRkAg0PFgQfAgVDPGgyPlBsZWFzZSBjb21wbGV0ZSB0aGVzZSBzZWN0aW9ucyB0byBnZXQgeW91ciBwcm9maWxlIHRvIDEwMCU8L2gyPh8EZ2QCDw9kFgJmDxYCHwIFMkltcHJvdmluZyB5b3VyIHByb2ZpbGUgaXMgZWFzeSwgZWZmZWN0aXZlIGFuZCBmcmVlZAIRDxQrAAIPFgQeC18hRGF0YUJvdW5kZx4LXyFJdGVtQ291bnQCBWRkFgJmD2QWCgIBD2QWBmYPFQEAZAIBDw8WBh8CBSZBZGQgYSBsb25nZXIgZGVzY3JpcHRpb24gKDUwMCBjaGFycyArKR8DBXIvVXBkYXRlRGV0YWlscy5hc3B4P2VkaXRTZWN0aW9uPURlc2NyaXB0aW9uJkNvbXBhbnlJRD00MzQ0NDEzNiZyZWRpcmVjdFVSTD0mcz1jdGEmbWs9TWVzc2FnZS5EZXNjcmlwdGlvbi5WZXJ5U2hvcnQeB1Rvb2xUaXAFJkFkZCBhIGxvbmdlciBkZXNjcmlwdGlvbiAoNTAwIGNoYXJzICspZGQCAg8VAQMzNSVkAgIPZBYGZg8VAQBkAgEPDxYGHwIFIEFkZCB5b3VyIGJ1c2luZXNzIGtleXdvcmRzICgxMCspHwMFYi9VcGRhdGVEZXRhaWxzLmFzcHg/ZWRpdFNlY3Rpb249S2V5d29yZHMmQ29tcGFueUlEPTQzNDQ0MTM2JnJlZGlyZWN0VVJMPSZzPWN0YSZtaz1NZXNzYWdlLlRhZ3MuRmV3HwwFIEFkZCB5b3VyIGJ1c2luZXNzIGtleXdvcmRzICgxMCspZGQCAg8VAQI1JWQCAw9kFgZmDxUBAGQCAQ8PFgYfAgUjQWRkIHlvdXIgcHJvZHVjdHMgYW5kIHNlcnZpY2VzICg1KykfAwV0L1ByZXNzUmVsZWFzZS5hc3B4P2VkaXRTZWN0aW9uPVVwZGF0ZSZhY3Rpb249bmV3JkNvbXBhbnlJRD00MzQ0NDEzNiZyZWRpcmVjdFVSTD0mcz1jdGEmbWs9TWVzc2FnZS5QcmVzc1JlbGVhc2VzLk5vbmUfDAUjQWRkIHlvdXIgcHJvZHVjdHMgYW5kIHNlcnZpY2VzICg1KylkZAICDxUBAzEyJWQCBA9kFgZmDxUBAGQCAQ8PFgYfAgUeQ29tcGxldGUgeW91ciBidXNpbmVzcyBhZGRyZXNzHwMFbS9VcGRhdGVEZXRhaWxzLmFzcHg/ZWRpdFNlY3Rpb249Q29udGFjdERldGFpbHMmQ29tcGFueUlEPTQzNDQ0MTM2JnJlZGlyZWN0VVJMPSZzPWN0YSZtaz1NZXNzYWdlLkFkZHJlc3MuU2hvcnQfDAUeQ29tcGxldGUgeW91ciBidXNpbmVzcyBhZGRyZXNzZGQCAg8VAQI1JWQCBQ9kFgZmDxUBAGQCAQ8PFgYfAgUYQWRkIHlvdXIgYnVzaW5lc3MgaW1hZ2VzHwMFTy9VcGxvYWRJbWFnZXMuYXNweD9Db21wYW55SUQ9NDM0NDQxMzYmcmVkaXJlY3RVUkw9JnM9Y3RhJm1rPU1lc3NhZ2UuSW1hZ2VzLk5vbmUfDAUYQWRkIHlvdXIgYnVzaW5lc3MgaW1hZ2VzZGQCAg8VAQIzJWQCDg9kFgJmDxYCHwRoFgICBw8WAh8EaGQCDw8WAh8EaGQCEA9kFhpmD2QWAmYPZBYCZg9kFgJmD2QWCGYPDxYCHwJlZGQCAQ8PFgIfAmVkZAICDw8WBB4PVmFsaWRhdGlvbkdyb3VwBQxzZWFyY2hGb290ZXIfCQUQaXNQb3N0YmFjaz10cnVlO2RkAgMPDxYEHgxFcnJvck1lc3NhZ2UFJFlvdSBtYXkgaGF2ZSBmb3Jnb3R0ZW4gYSBzZWFyY2ggdGVybR8NBQxzZWFyY2hGb290ZXJkZAIDDxYCHwRnFggCAQ8WBB8GBSJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9Ib3Rmcm9nVVNBHwcFFUZvbGxvdyB1cyBvbiBGYWNlYm9va2QCAg8WBB8GBSJodHRwczovL3R3aXR0ZXIuY29tLyMhL2hvdGZyb2dpbmZvHwcFFEZvbGxvdyB1cyBvbiBUd2l0dGVyZAIDDxYEHwYFSGh0dHA6Ly93d3cubGlua2VkaW4uY29tL2NvbXBhbnkvaG90ZnJvZy0tLXRoZS13b3JsZCdzLWJ1c2luZXNzLWRpcmVjdG9yeR8HBRVGb2xsb3cgdXMgb24gTGlua2VkSW5kAgQPFgQfBgUtaHR0cHM6Ly9wbHVzLmdvb2dsZS5jb20vMTEzNDgxODIwNDg4MTM0MTMwNDk3HwcFFEZvbGxvdyB1cyBvbiBHb29nbGUrZAIEDxYCHwRnFgJmDxYEHwYFDS9BYm91dFVTLmFzcHgeA3JlbAUIbm9mb2xsb3dkAgUPFgIfBGcWAmYPFgQfBgUWL0hvdGZyb2dQcm9tb3Rpb24uYXNweB8PZWQCBg8WAh8EZxYCZg8WBB8GBRkvQWR2ZXJ0aXNpbmdQYXJ0bmVycy5hc3B4Hw9lZAIIDxYCHwRnFgJmDxYEHwYFCy9UZXJtcy5hc3B4Hw8FCG5vZm9sbG93ZAIJDxYCHwRnFgJmDxYEHwYFDS9Qcml2YWN5LmFzcHgfDwUIbm9mb2xsb3dkAgwPFgIfBGcWAmYPFgYfBgU5aHR0cDovL3N1cHBvcnQuaG90ZnJvZy5jb20vY3VzdG9tZXIvZW5fdXMvcG9ydGFsL2FydGljbGVzHw8FCG5vZm9sbG93HgZ0YXJnZXQFBl9ibGFua2QCEA8WBh8GBRZodHRwOi8vaG90ZnJvZy5jb20vc2JoHw9lHwRnZAIRDxYCHwRnFgJmDxYEHwYFM2h0dHA6Ly9ob3Rmcm9nLmNvbS9zYmgvY2F0ZWdvcnkvZ3Jvdy15b3VyLWJ1c2luZXNzLx8PZWQCEg8WAh8EZxYCZg8WBB8GBSpodHRwOi8vaG90ZnJvZy5jb20vc2JoL2NhdGVnb3J5L21hcmtldGluZy8fD2VkAhMPFgIfBGcWAmYPFgQfBgVUaHR0cDovL3N1cHBvcnQuaG90ZnJvZy5jb20vY3VzdG9tZXIvZW5fdXMvcG9ydGFsL3RvcGljcy84MDU1NTEtaG90ZnJvZy10aXBzL2FydGljbGVzHw9lZAIVDw8WAh8CBQ8xNy4wLjAuMDAxIC0gNDJkZAICDw8WAh8EaGRkAgMPDxYCHwRoZGQCBA9kFgJmDxYCHwRoZAIFD2QWAmYPFgIfCwIBFgICAQ9kFgJmDxUBDlVBLTIxNDEyMDYxLTM0ZBgDBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAQU6Y3RsMDAkY29udGVudFNlY3Rpb24kY3RybENvbnRhY3REZXRhaWxzJGNoYk5vVGVsZW1hcmtldGVycwUiY3RsMDAkQ2FsbFRvQWN0aW9uMSRBY3Rpb25NZXNzYWdlcw8UKwAOZGRmZGRkZDwrAAUAAgVkZGRmAv////8PZAUeY3RsMDAkY29udGVudFNlY3Rpb24kTXVsdGlWaWV3Dw9kAgFksjGYEzaEW2gSpP2/B3XFynYcdJY=';
//            $params['ctl00$contentSection$ctrlContactDetails$hiddenX'] = $business->getLatitude();
//            $params['ctl00$contentSection$ctrlContactDetails$hiddenY'] = $business->getLongitude();
//            $params['ctl00$contentSection$ctrlContactDetails$hiddenAccuracy'] = 4;
//            $params['l00$contentSection$ctrlContactDetails$hiddenCountry'] = '';
//            $params['ctl00$contentSection$ctrlContactDetails$txtBusinessName'] = $business->getName();
//            $params['ctl00$contentSection$ctrlContactDetails$txtStreetAddress'] = $business->getAddress();
//            $params['ctl00$contentSection$ctrlContactDetails$txtAddress2'] = '';
//            $params['ctl00$contentSection$ctrlContactDetails$txtAddress3'] = '';
//            $params['ctl00$contentSection$ctrlContactDetails$txtSuburb'] = $business->getLocality();
//            $params['ctl00$contentSection$ctrlContactDetails$cboState'] = 'AL';
//            $params['ctl00$contentSection$ctrlContactDetails$txtPostcode'] = 36310;
//            $params['ctl00$contentSection$ctrlContactDetails$txtPhone'] = $business->getPhoneNumber();
//            $params['ctl00$contentSection$ctrlContactDetails$txtFax'] = '';
//            $params['ctl00$contentSection$ctrlContactDetails$txtWebsite'] = $business->getWebsite();
//            $params['ctl00$contentSection$ctrlContactDetails$txtEmail'] = $business->getEmail();
//            $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 1;
            $params['ctl00$contentSection$ctrlContactDetails$hiddenX'] = -37.8121636;
            $params['ctl00$contentSection$ctrlContactDetails$hiddenY'] = 144.9724132;
            $params['ctl00$contentSection$ctrlContactDetails$hiddenAccuracy'] = 4;
            $params['l00$contentSection$ctrlContactDetails$hiddenCountry'] = '';
            $params['ctl00$contentSection$ctrlContactDetails$txtBusinessName'] = $business->getName();
            $params['ctl00$contentSection$ctrlContactDetails$txtStreetAddress'] = 'San Telmo, 14 Meyers Pl, Melbourne Victoria, Australia';
            $params['ctl00$contentSection$ctrlContactDetails$txtAddress2'] = '';
            $params['ctl00$contentSection$ctrlContactDetails$txtAddress3'] = '';
            $params['ctl00$contentSection$ctrlContactDetails$txtSuburb'] = 'Hackett';
            $params['ctl00$contentSection$ctrlContactDetails$cboState'] = 'AL';
            $params['ctl00$contentSection$ctrlContactDetails$txtPostcode'] = 36310;
            $params['ctl00$contentSection$ctrlContactDetails$txtPhone'] = $business->getPhoneNumber();
            $params['ctl00$contentSection$ctrlContactDetails$txtFax'] = '';
            $params['ctl00$contentSection$ctrlContactDetails$txtWebsite'] = $business->getWebsite();
            $params['ctl00$contentSection$ctrlContactDetails$txtEmail'] = $business->getEmail();
            $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 1;
            $this->curl->setCookies($cookies);
            $this->curl->post($this->urlEditBusiness . $hotfrogAccount->getBusinessId(), $params);
            if(strpos($this->curl->response, 'Login.aspx') && $notLoop === false){
                $this->auth($hotfrogAccount);
                $this->editAccount($hotfrogAccount, $business, true);
            }else{
                throw new OAuthCompanyException($this->message);
            }
        } else {
            throw new OAuthCompanyException($this->message);
        }
    }

    /**
     * @param $content
     * @param $inputName
     * @param HotfrogAccount $hotfrogAccount
     * @return mixed
     * @throws OAuthCompanyException
     */
    private function getCSRFToken($content, $inputName, HotfrogAccount $hotfrogAccount, $business)
    {

        if ($inputName == 'meta') {
            $first_step = explode('<meta name="csrf-token"', $content);
            var_dump($first_step);
            $second_step = explode(' />', $first_step[1]);
            $csrf = str_replace('"', '', $second_step[0]);
        } else {
            $first_step = explode($inputName, $content);
            $second_step = explode('value="', $first_step[2]);
            $third_step = explode('" />', $second_step[1]);
            $csrf = str_replace('"', '', $third_step[0]);
        }


        return $csrf;
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
        $this->curl->setCookies(array_merge($this->session->get('coocie_hotfrog'),['HotFrogClickThrough' => 'OriginalReferrer=https://www.hotfrog.com/AddYourBusinessSingle.aspx']));
        $params['__LASTFOCUS'] = '';
        $params['__EVENTTARGET'] = 'ctl00$contentSection$btnFinish';
        $params['__EVENTARGUMENT'] = '';
        $params['__VIEWSTATE'] = '/wEPDwULLTIxMTc0NjMzMjIPFgIeCFdvcmtmbG93MvcsAAEAAAD/////AQAAAAAAAAAMAgAAAEdIb3RGcm9nLkJ1c2luZXNzLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbAUBAAAAMEhvdEZyb2cuQnVzaW5lc3MuQWRkWW91ckJ1c2luZXNzLldvcmtmbG93TWFuYWdlcgUAAAAQX2J1c2luZXNzTGlzdGluZwpfc2Vzc2lvbklkEF9pc0FkZEFuZFVwZ3JhZGUTX0lzUmVnaXN0ZXJBbmRMb2dpbgpfcHJvbW9Db2RlBAEAAAEwSG90RnJvZy5CdXNpbmVzcy5BZGRZb3VyQnVzaW5lc3MuQnVzaW5lc3NMaXN0aW5nAgAAAAEBAgAAAAkDAAAABgQAAAAYcXpqenlhbXlocDR0azBqNGlnbGhka25nAAAKDAUAAABFSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsBQMAAAAwSG90RnJvZy5CdXNpbmVzcy5BZGRZb3VyQnVzaW5lc3MuQnVzaW5lc3NMaXN0aW5nDQAAABBfSXNBZGRBbmRVcGdyYWRlE19Jc1JlZ2lzdGVyQW5kTG9naW4IX3NlY3Rpb24QX2Z1cnRoZXN0U2VjdGlvbghfY29tcGFueQ1fYnVzaW5lc3NUeXBlCF9waHJhc2VzBV91c2VyEV91c2VyU3Vic2NyaXB0aW9uD191c2VyVHJhY2tpbmdJRBlfdXNlclRyYWNraW5nQWN0aXZpdHlUeXBlDF9xdWVyeVN0cmluZwpfcHJvbW9Db2RlAAAEBAQEAwQDAAQBAQEBLEhvdEZyb2cuQnVzaW5lc3MuQWRkWW91ckJ1c2luZXNzLlNlY3Rpb25UeXBlAgAAACxIb3RGcm9nLkJ1c2luZXNzLkFkZFlvdXJCdXNpbmVzcy5TZWN0aW9uVHlwZQIAAAAgSG90RnJvZy5Db21tb24uRFRPLkNvbXBhbnlFbnRpdHkFAAAAJUhvdEZyb2cuQ29tbW9uLkRUTy5CdXNpbmVzc1R5cGVFbnRpdHkFAAAAiwFTeXN0ZW0uQ29sbGVjdGlvbnMuR2VuZXJpYy5MaXN0YDFbW0hvdEZyb2cuQ29tbW9uLkRUTy5QaHJhc2VFbnRpdHksIEhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbF1dHUhvdEZyb2cuQ29tbW9uLkRUTy5Vc2VyRW50aXR5BQAAAI4BU3lzdGVtLkNvbGxlY3Rpb25zLkdlbmVyaWMuTGlzdGAxW1tIb3RGcm9nLkNvbW1vbi5EVE8uVXNlck9wdGluRW50aXR5LCBIb3RGcm9nLkNvbW1vbiwgVmVyc2lvbj0xLjAuMC4wLCBDdWx0dXJlPW5ldXRyYWwsIFB1YmxpY0tleVRva2VuPW51bGxdXQg1SG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzTGlzdGluZ1VzZXJUcmFja2VyQWN0aXZpdHkFAAAAAgAAAAAABfr///8sSG90RnJvZy5CdXNpbmVzcy5BZGRZb3VyQnVzaW5lc3MuU2VjdGlvblR5cGUBAAAAB3ZhbHVlX18ACAIAAAAgAAAAAfn////6////AAAAAAkIAAAACQkAAAAJCgAAAAkLAAAACQwAAAAAAAAABfP///81SG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzTGlzdGluZ1VzZXJUcmFja2VyQWN0aXZpdHkBAAAAB3ZhbHVlX18ACAUAAAABAAAACgoFCAAAACBIb3RGcm9nLkNvbW1vbi5EVE8uQ29tcGFueUVudGl0eRwAAAAaPENvbXBhbnlJZD5rX19CYWNraW5nRmllbGQYPEFkZHJlc3M+a19fQmFja2luZ0ZpZWxkFjxQaG9uZT5rX19CYWNraW5nRmllbGQpPFBob25lVmFsaWRhdGlvbkRpc3BsYXllZD5rX19CYWNraW5nRmllbGQUPEZheD5rX19CYWNraW5nRmllbGQWPEVtYWlsPmtfX0JhY2tpbmdGaWVsZCk8RW1haWxWYWxpZGF0aW9uRGlzcGxheWVkPmtfX0JhY2tpbmdGaWVsZBg8V2Vic2l0ZT5rX19CYWNraW5nRmllbGQfPERpc3BsYXlXZWJzaXRlPmtfX0JhY2tpbmdGaWVsZCs8V2Vic2l0ZVZhbGlkYXRpb25EaXNwbGF5ZWQ+a19fQmFja2luZ0ZpZWxkHDxEZXNjcmlwdGlvbj5rX19CYWNraW5nRmllbGQiPElzVmFsaWRXZWJBZGRyZXNzPmtfX0JhY2tpbmdGaWVsZBw8T3duZXJzaGlwSWQ+a19fQmFja2luZ0ZpZWxkHjxJc1BhaWRMaXN0aW5nPmtfX0JhY2tpbmdGaWVsZCM8Q3VycmVudExpc3RpbmdUeXBlPmtfX0JhY2tpbmdGaWVsZBw8Q29tcGFueVJhbms+a19fQmFja2luZ0ZpZWxkHDxSZWxhdGVkVGFncz5rX19CYWNraW5nRmllbGQcPENvbXBhbnlMb2dvPmtfX0JhY2tpbmdGaWVsZBw8Q29tcGFueUd1aWQ+a19fQmFja2luZ0ZpZWxkIDxJc05lYXJieUNvbXBhbnk+a19fQmFja2luZ0ZpZWxkGzxSZWdpb25MaXN0PmtfX0JhY2tpbmdGaWVsZB08Tm9PZkVtcGxveWVlPmtfX0JhY2tpbmdGaWVsZCA8Tm9UZWxlbWFya2V0ZXJzPmtfX0JhY2tpbmdGaWVsZCpDb21wYW55QmFzZWAxKzxDb21wYW55TmFtZT5rX19CYWNraW5nRmllbGQxQ29tcGFueUJhc2VgMSs8VXJsU2FmZUNvbXBhbnlOYW1lPmtfX0JhY2tpbmdGaWVsZCtDb21wYW55QmFzZWAxKzxVcmxTYWZlU3RhdGU+a19fQmFja2luZ0ZpZWxkLENvbXBhbnlCYXNlYDErPFVybFNhZmVTdWJ1cmI+a19fQmFja2luZ0ZpZWxkHUNvbXBhbnlCYXNlYDErX3RoaXJkUGFydHlEYXRhAAQBAAEBAAEBAAEAAAAEAAEEAwABAAABAQEBBAggSG90RnJvZy5Db21tb24uRFRPLkFkZHJlc3NFbnRpdHkFAAAAAQEBAQgBJUhvdEZyb2cuQ29tbW9uLkRUTy5Db21wYW55TGlzdGluZ1R5cGUFAAAACCVIb3RGcm9nLkNvbW1vbi5EVE8uQ29tcGFueUltYWdlRW50aXR5BQAAAAtTeXN0ZW0uR3VpZAEIASxIb3RGcm9nLkNvbW1vbi5EVE8uVGhpcmRQYXJ0eS5UaGlyZFBhcnR5RGF0YQUAAAAFAAAAAAAAAAkOAAAABg8AAAAAAAkPAAAACQ8AAAAACQ8AAAAKAAkPAAAAAAAAAAAABfD///8lSG90RnJvZy5Db21tb24uRFRPLkNvbXBhbnlMaXN0aW5nVHlwZQEAAAAHdmFsdWVfXwAIBQAAAAEAAAAAAAAACgoE7////wtTeXN0ZW0uR3VpZAsAAAACX2ECX2ICX2MCX2QCX2UCX2YCX2cCX2gCX2kCX2oCX2sAAAAAAAAAAAAAAAgHBwICAgICAgICAAAAAAAAAAAAAAAAAAAAAAAJDwAAAAAAAAAACQ8AAAAJDwAAAAkPAAAACQ8AAAAKBQkAAAAlSG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzVHlwZUVudGl0eQkAAAAfPEJ1c2luZXNzVHlwZUlkPmtfX0JhY2tpbmdGaWVsZCU8UGFyZW50QnVzaW5lc3NUeXBlSWQ+a19fQmFja2luZ0ZpZWxkHDxMZXZlbE51bWJlcj5rX19CYWNraW5nRmllbGQhPEJ1c2luZXNzVHlwZU5hbWU+a19fQmFja2luZ0ZpZWxkJTxCdXNpbmVzc1R5cGVTdGF0dXNJZD5rX19CYWNraW5nRmllbGQYPFRvcGljSWQ+a19fQmFja2luZ0ZpZWxkJTxCdXNpbmVzc1R5cGVTb3VyY2VJZD5rX19CYWNraW5nRmllbGQWPElzTmV3PmtfX0JhY2tpbmdGaWVsZCM8Q2hpbGRCdXNpbmVzc1R5cGVzPmtfX0JhY2tpbmdGaWVsZAMDAAEAAAAAAwtTeXN0ZW0uR3VpZAtTeXN0ZW0uR3VpZAgICAgBkQFTeXN0ZW0uQ29sbGVjdGlvbnMuR2VuZXJpYy5MaXN0YDFbW0hvdEZyb2cuQ29tbW9uLkRUTy5CdXNpbmVzc1R5cGVFbnRpdHksIEhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbF1dBQAAAAHu////7////wAAAAAAAAAAAAAAAAAAAAAB7f///+////8AAAAAAAAAAAAAAAAAAAAAAAAAAAkPAAAAAAAAAAAAAAAAAAAAAAkUAAAABAoAAACLAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlBocmFzZUVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0DAAAABl9pdGVtcwVfc2l6ZQhfdmVyc2lvbgQAACFIb3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5W10FAAAACAgJFQAAAAAAAAAAAAAABQsAAAAdSG90RnJvZy5Db21tb24uRFRPLlVzZXJFbnRpdHkPAAAAFzxVc2VySWQ+a19fQmFja2luZ0ZpZWxkGTxVc2VybmFtZT5rX19CYWNraW5nRmllbGQZPFBhc3N3b3JkPmtfX0JhY2tpbmdGaWVsZBY8RW1haWw+a19fQmFja2luZ0ZpZWxkITxQYXNzd29yZFF1ZXN0aW9uPmtfX0JhY2tpbmdGaWVsZB88UGFzc3dvcmRBbnN3ZXI+a19fQmFja2luZ0ZpZWxkFjxUaXRsZT5rX19CYWNraW5nRmllbGQZPEpvYlRpdGxlPmtfX0JhY2tpbmdGaWVsZB48Sm9iVGl0bGVBZEhvYz5rX19CYWNraW5nRmllbGQVPFR5cGU+a19fQmFja2luZ0ZpZWxkHDxDb21wYW55TmFtZT5rX19CYWNraW5nRmllbGQaPEZpcnN0TmFtZT5rX19CYWNraW5nRmllbGQZPExhc3ROYW1lPmtfX0JhY2tpbmdGaWVsZBs8SXNWZXJpZmllZD5rX19CYWNraW5nRmllbGQbPE9wdGluVHlwZXM+a19fQmFja2luZ0ZpZWxkAwEBAQEBAQQBBAEBAQADC1N5c3RlbS5HdWlkIUhvdEZyb2cuQ29tbW9uLkRUTy5Kb2JUaXRsZUVudGl0eQUAAAAbSG90RnJvZy5Db21tb24uRFRPLlVzZXJUeXBlBQAAAAGOAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlVzZXJPcHRpbkVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0FAAAAAer////v////AAAAAAAAAAAAAAAAAAAAAAoKCgoKCgoKBen///8bSG90RnJvZy5Db21tb24uRFRPLlVzZXJUeXBlAQAAAAd2YWx1ZV9fAAgFAAAAAwAAAAoKCgAKBAwAAACOAVN5c3RlbS5Db2xsZWN0aW9ucy5HZW5lcmljLkxpc3RgMVtbSG90RnJvZy5Db21tb24uRFRPLlVzZXJPcHRpbkVudGl0eSwgSG90RnJvZy5Db21tb24sIFZlcnNpb249MS4wLjAuMCwgQ3VsdHVyZT1uZXV0cmFsLCBQdWJsaWNLZXlUb2tlbj1udWxsXV0DAAAABl9pdGVtcwVfc2l6ZQhfdmVyc2lvbgQAACRIb3RGcm9nLkNvbW1vbi5EVE8uVXNlck9wdGluRW50aXR5W10FAAAACAgJGAAAAAIAAAACAAAABQ4AAAAgSG90RnJvZy5Db21tb24uRFRPLkFkZHJlc3NFbnRpdHkLAAAAG19hZGRyZXNzVmFsaWRhdGlvbkRpc3BsYXllZAlfYWRkcmVzczEJX2FkZHJlc3MyCV9hZGRyZXNzMwdfc3VidXJiBl9zdGF0ZQlfcG9zdGNvZGUIX2NvdW50cnkIX2dlb2NvZGUJX2Rpc3RhbmNlFl9sb2NhdGlvbkFjY3VyYWN5TGV2ZWwAAQEBAQEBAQQAAAEgSG90RnJvZy5Db21tb24uRFRPLkdlb2NvZGVFbnRpdHkFAAAABQgFAAAAAAoKCgoKBhkAAAAFODMyMTAKCgEwAAAAAAQUAAAAkQFTeXN0ZW0uQ29sbGVjdGlvbnMuR2VuZXJpYy5MaXN0YDFbW0hvdEZyb2cuQ29tbW9uLkRUTy5CdXNpbmVzc1R5cGVFbnRpdHksIEhvdEZyb2cuQ29tbW9uLCBWZXJzaW9uPTEuMC4wLjAsIEN1bHR1cmU9bmV1dHJhbCwgUHVibGljS2V5VG9rZW49bnVsbF1dAwAAAAZfaXRlbXMFX3NpemUIX3ZlcnNpb24EAAAnSG90RnJvZy5Db21tb24uRFRPLkJ1c2luZXNzVHlwZUVudGl0eVtdBQAAAAgICRoAAAAAAAAAAAAAAAcVAAAAAAEAAAAAAAAABB9Ib3RGcm9nLkNvbW1vbi5EVE8uUGhyYXNlRW50aXR5BQAAAAcYAAAAAAEAAAAEAAAABCJIb3RGcm9nLkNvbW1vbi5EVE8uVXNlck9wdGluRW50aXR5BQAAAAkbAAAACRwAAAANAgcaAAAAAAEAAAAAAAAABCVIb3RGcm9nLkNvbW1vbi5EVE8uQnVzaW5lc3NUeXBlRW50aXR5BQAAAAUbAAAAIkhvdEZyb2cuQ29tbW9uLkRUTy5Vc2VyT3B0aW5FbnRpdHkCAAAAFjxPcHRpbj5rX19CYWNraW5nRmllbGQYPElzT3B0aW4+a19fQmFja2luZ0ZpZWxkBAAiSG90RnJvZy5Db21tb24uRFRPLk9wdGluVHlwZUVudGl0eQUAAAABBQAAAAkdAAAAAQEcAAAAGwAAAAkeAAAAAQUdAAAAIkhvdEZyb2cuQ29tbW9uLkRUTy5PcHRpblR5cGVFbnRpdHkDAAAAHDxPcHRpblR5cGVJZD5rX19CYWNraW5nRmllbGQfPFRyYW5zbGF0aW9uS2V5PmtfX0JhY2tpbmdGaWVsZB08RGVmYXVsdE9wdGluPmtfX0JhY2tpbmdGaWVsZAABAAgBBQAAAAEAAAAGHwAAABhzdWJzY3JpYmVUb0hvdEZyb2dPZmZlcnMBAR4AAAAdAAAAAgAAAAYgAAAAG3N1YnNjcmliZUhvdEZyb2dOZXdzbGV0dGVycwELFgJmD2QWCGYPZBYCAgMPFgIeB1Zpc2libGVoZAICEGRkFgYCAQ9kFgZmD2QWCGYPFgIfAWhkAgEPDxYEHgRUZXh0BQNIdWIeC05hdmlnYXRlVXJsBRZodHRwOi8vaG90ZnJvZy5jb20vc2JoZGQCAg8PFgQfAgUFTG9naW4fAwUiaHR0cHM6Ly93d3cuaG90ZnJvZy5jb20vTG9naW4uYXNweBYCHgV0aXRsZQUFTG9naW5kAgMPFgIfAWhkAgEPFgIfAWcWAmYPZBYCZg9kFgJmD2QWBAICDw8WBB4PVmFsaWRhdGlvbkdyb3VwBQxzZWFyY2hIZWFkZXIeDU9uQ2xpZW50Q2xpY2sFEGlzUG9zdGJhY2s9dHJ1ZTtkZAIDDw8WBB4MRXJyb3JNZXNzYWdlBSRZb3UgbWF5IGhhdmUgZm9yZ290dGVuIGEgc2VhcmNoIHRlcm0fBQUMc2VhcmNoSGVhZGVyZGQCAg8WAh8BZ2QCAg9kFhwCAg8WAh8CBS1CZSBmb3VuZCBieSBjdXN0b21lcnMgbG9va2luZyBmb3Igd2hhdCB5b3UgZG9kAgQPDxYCHwJlZGQCBg9kFgQCAQ9kFggCBQ9kFgRmDw8WAh8BaGRkAgMPDxYCHwFoZGQCBg9kFgRmDw8WAh8BaGRkAgMPDxYCHwFoZGQCBw9kFgRmDw8WAh8BaGRkAgMPDxYCHwFoZGQCCg9kFgICBA8PFgIeFFZhbGlkYXRpb25FeHByZXNzaW9uBRBeXGR7NX0oLVxkezR9KT8kZGQCAw9kFgICCA8PFgIfCAUsXHcrKFstKy4nXVx3KykqQFx3KyhbLS5dXHcrKSpcLlx3KyhbLS5dXHcrKSpkZAIHDxYCHwFoFgICAw8PFgIfBgUQaXNQb3N0YmFjaz10cnVlO2RkAggPFgIfAWhkAgoPZBYIZg9kFgICBg9kFggCAQ8QDxYCHgtfIURhdGFCb3VuZGcWAh4Ib25jaGFuZ2UF/QIgR2VuZXJhbEZvcm0uVmFsaWRhdGlvbi5kaXNwbGF5KCk7IFNldEpvYlRpdGxlQWRIb2NWaXNpYmlsaXR5X2N0bDAwX2NvbnRlbnRTZWN0aW9uX0xvZ2luUGFzc3dvcmRDb250cm9sX0pvYlRpdGxlX2RkbEpvYlRpdGxlKCk7IEdlbmVyYWxGb3JtLlZhbGlkYXRpb24uZGlzcGxheSgpOyBTZXRKb2JUaXRsZUFkSG9jVmlzaWJpbGl0eV9jdGwwMF9jb250ZW50U2VjdGlvbl9Mb2dpblBhc3N3b3JkQ29udHJvbF9Kb2JUaXRsZV9kZGxKb2JUaXRsZSgpOyBHZW5lcmFsRm9ybS5WYWxpZGF0aW9uLmRpc3BsYXkoKTsgU2V0Sm9iVGl0bGVBZEhvY1Zpc2liaWxpdHlfY3RsMDBfY29udGVudFNlY3Rpb25fTG9naW5QYXNzd29yZENvbnRyb2xfSm9iVGl0bGVfZGRsSm9iVGl0bGUoKTsQFQsNUGxlYXNlIHNlbGVjdCNCdXNpbmVzcyBvd25lciBvciBzZW5pb3IgbWFuYWdlbWVudAVTYWxlcwlNYXJrZXRpbmcPSHVtYW4gcmVzb3VyY2VzB0ZpbmFuY2UCSVQVT2ZmaWNlIGFkbWluaXN0cmF0aW9uElBlcnNvbmFsIGFzc2lzdGFudBRMZWdhbCByZXByZXNlbnRhdGl2ZQVPdGhlchULDVBsZWFzZSBzZWxlY3QaQnVzaW5lc3NPd25lclNlbmlvck1hbmFnZXIFU2FsZXMJTWFya2V0aW5nDkh1bWFuUmVzb3VyY2VzB0ZpbmFuY2UVSW5mb3JtYXRpb25UZWNobm9sb2d5C09mZmljZUFkbWluGlBlcnNvbmFsQXNzaXN0YW50U2VjcmV0YXJ5E0xlZ2FsUmVwcmVzZW50YXRpdmUFT3RoZXIUKwMLZ2dnZ2dnZ2dnZ2dkZAIDDw9kFgIfCgVmIEdlbmVyYWxGb3JtLlZhbGlkYXRpb24uZGlzcGxheSgpOyBHZW5lcmFsRm9ybS5WYWxpZGF0aW9uLmRpc3BsYXkoKTsgR2VuZXJhbEZvcm0uVmFsaWRhdGlvbi5kaXNwbGF5KCk7ZAIEDw8WAh8HBSRKdXN0IGluIGNhc2Ugd2UgbmVlZCB0byBjb250YWN0IHlvdS5kZAIFDw8WBB8HBSRKdXN0IGluIGNhc2Ugd2UgbmVlZCB0byBjb250YWN0IHlvdS4eDEluaXRpYWxWYWx1ZQUNUGxlYXNlIHNlbGVjdGRkAgQPDxYCHwgFLFx3KyhbLSsuJ11cdyspKkBcdysoWy0uXVx3KykqXC5cdysoWy0uXVx3KykqZGQCBg9kFgQCAQ8PZBYCHgV2YWx1ZQUHMTJhZG1pbmQCBg8PZBYCHwwFBzEyYWRtaW5kAgcPFgIfAWgWBAICDw8WAh4HRW5hYmxlZGhkZAIFDw8WAh8NaGRkAgsPFgIfAWhkAgwPFgIfAWhkAg0PDxYCHwIFGUNvbW11bmljYXRpb24gcHJlZmVyZW5jZXNkZAIODw8WAh8CBTJQbGVhc2Ugc2VsZWN0IHlvdXIgY29tbXVuaWNhdGlvbiBwcmVmZXJlbmNlcyBiZWxvd2RkAg8PZBYCAgEPZBYCZg8WAh4LXyFJdGVtQ291bnQCAhYEAgEPZBYCAgEPEA8WAh8CBawBSSB3b3VsZCBsaWtlIHRvIHJlY2VpdmUgbW9udGhseSByZXBvcnRzIG9uIHRoZSBwZXJmb3JtYW5jZSBvZiBteSBIb3Rmcm9nIHByb2ZpbGUgYXMgd2VsbCBhcyBuZXdzLCBpbmZvcm1hdGlvbiBhbmQgdGlwcyBvbiBob3cgdG8gaW1wcm92ZSBteSBwcm9maWxlIGFuZCBteSBvbmxpbmUgbWFya2V0aW5nLmRkZGQCAg9kFgICAQ8QDxYCHwIFkwFJIHdvdWxkIGxpa2UgdG8gcmVjZWl2ZSByZWxldmFudCBvZmZlcnMgYW5kIG9jY2FzaW9uYWwgaGVscGZ1bCB0aXBzIGZyb20gSG90ZnJvZyBwYXJ0bmVycyB2aWEgSG90ZnJvZyBEYXRhIFNlcnZpY2UsIGVNZWRpYSBhbmQgb3RoZXIgdGhpcmQtcGFydGllcy5kZGRkAhAPZBYCAgEPZBYCZg9kFgJmD2QWAgIBDw9kFgIeB29ua2V5dXAFJnRoaXMudmFsdWUgPSB0aGlzLnZhbHVlLnRvTG93ZXJDYXNlKCk7ZAIRDxYCHwIFkQJXaGVuIHlvdSBhZGQgeW91ciBwcm9maWxlIHRvIEhvdGZyb2csIHlvdSBhcmUgYWNjZXB0aW5nIG91ciA8YSBocmVmPSIvVGVybXMuYXNweCIgdGFyZ2V0PSJfYmxhbmsiPlRlcm1zIG9mIFVzZTwvYT4gYW5kIDxhIGhyZWY9Ii9Qcml2YWN5LmFzcHgiIHRhcmdldD0iX2JsYW5rIj5Qcml2YWN5IFBvbGljeTwvYT4uIFdlIHdpbGwgc2VuZCB5b3UgcmVwb3J0cywgdGlwcyBhbmQgbmV3cyB2aWEgZW1haWwuIENsaWNrIOKAmFN1Ym1pdOKAmSB0byBhZGQgeW91ciBwcm9maWxlIG5vdy5kAhIPZBYCAgEPDxYCHwYFEGlzUG9zdGJhY2s9dHJ1ZTtkZAIED2QWGmYPZBYCZg9kFgJmD2QWAmYPZBYEAgIPDxYEHwUFDHNlYXJjaEZvb3Rlch8GBRBpc1Bvc3RiYWNrPXRydWU7ZGQCAw8PFgQfBwUkWW91IG1heSBoYXZlIGZvcmdvdHRlbiBhIHNlYXJjaCB0ZXJtHwUFDHNlYXJjaEZvb3RlcmRkAgMPFgIfAWcWCAIBDxYEHgRocmVmBSJodHRwOi8vd3d3LmZhY2Vib29rLmNvbS9Ib3Rmcm9nVVNBHwQFFUZvbGxvdyB1cyBvbiBGYWNlYm9va2QCAg8WBB8QBSJodHRwczovL3R3aXR0ZXIuY29tLyMhL2hvdGZyb2dpbmZvHwQFFEZvbGxvdyB1cyBvbiBUd2l0dGVyZAIDDxYEHxAFSGh0dHA6Ly93d3cubGlua2VkaW4uY29tL2NvbXBhbnkvaG90ZnJvZy0tLXRoZS13b3JsZCdzLWJ1c2luZXNzLWRpcmVjdG9yeR8EBRVGb2xsb3cgdXMgb24gTGlua2VkSW5kAgQPFgQfEAUtaHR0cHM6Ly9wbHVzLmdvb2dsZS5jb20vMTEzNDgxODIwNDg4MTM0MTMwNDk3HwQFFEZvbGxvdyB1cyBvbiBHb29nbGUrZAIEDxYCHwFnFgJmDxYEHxAFDS9BYm91dFVTLmFzcHgeA3JlbAUIbm9mb2xsb3dkAgUPFgIfAWcWAmYPFgQfEAUWL0hvdGZyb2dQcm9tb3Rpb24uYXNweB8RZWQCBg8WAh8BZxYCZg8WBB8QBRkvQWR2ZXJ0aXNpbmdQYXJ0bmVycy5hc3B4HxFlZAIIDxYCHwFnFgJmDxYEHxAFCy9UZXJtcy5hc3B4HxEFCG5vZm9sbG93ZAIJDxYCHwFnFgJmDxYEHxAFDS9Qcml2YWN5LmFzcHgfEQUIbm9mb2xsb3dkAgwPFgIfAWcWAmYPFgYfEAU5aHR0cDovL3N1cHBvcnQuaG90ZnJvZy5jb20vY3VzdG9tZXIvZW5fdXMvcG9ydGFsL2FydGljbGVzHxEFCG5vZm9sbG93HgZ0YXJnZXQFBl9ibGFua2QCEA8WBh8QBRZodHRwOi8vaG90ZnJvZy5jb20vc2JoHxFlHwFnZAIRDxYCHwFnFgJmDxYEHxAFM2h0dHA6Ly9ob3Rmcm9nLmNvbS9zYmgvY2F0ZWdvcnkvZ3Jvdy15b3VyLWJ1c2luZXNzLx8RZWQCEg8WAh8BZxYCZg8WBB8QBSpodHRwOi8vaG90ZnJvZy5jb20vc2JoL2NhdGVnb3J5L21hcmtldGluZy8fEWVkAhMPFgIfAWcWAmYPFgQfEAVUaHR0cDovL3N1cHBvcnQuaG90ZnJvZy5jb20vY3VzdG9tZXIvZW5fdXMvcG9ydGFsL3RvcGljcy84MDU1NTEtaG90ZnJvZy10aXBzL2FydGljbGVzHxFlZAIVDw8WAh8CBQ8xNy4wLjAuMDAxIC0gNjRkZAIEDw8WAh8BaGRkAgUPDxYCHwFoZGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgMFPWN0bDAwJGNvbnRlbnRTZWN0aW9uJENvbnRhY3REZXRhaWxzQ29udHJvbCRjaGJOb1RlbGVtYXJrZXRlcnMFSWN0bDAwJGNvbnRlbnRTZWN0aW9uJFVzZXJTdWJzcmlwdGlvbiRzdWJzY3JpcHRpb25MaXN0JGN0bDAxJG9wdGluQ2hlY2tCb3gFSWN0bDAwJGNvbnRlbnRTZWN0aW9uJFVzZXJTdWJzcmlwdGlvbiRzdWJzY3JpcHRpb25MaXN0JGN0bDAyJG9wdGluQ2hlY2tCb3hYSU5BZ8tP9yctcgFLsAA4bc0+Bw==';
        $params['__VIEWSTATEGENERATOR'] = ' B424CFAF';
        $params['ctl00$hotFrogHeader$hotfrogSearch$txtWhat'] = '';
        $params['ctl00$hotFrogHeader$hotfrogSearch$txtWhere'] = '';
//        $params['ctl00$contentSection$CompanyDetailsControl$txtBusinessName'] = $business->getName();
//        $params['ctl00$contentSection$CompanyDetailsControl$txtStreetAddress'] = $business->getAddress();
//        $params['ctl00$contentSection$CompanyDetailsControl$txtAddress2'] = '';
//        $params['ctl00$contentSection$CompanyDetailsControl$txtAddress3'] = '';
//        $params['ctl00$contentSection$CompanyDetailsControl$txtSuburb'] = $business->getLocality();
//        $params['ctl00$contentSection$CompanyDetailsControl$cboState'] = 'AL';
//        $params['ctl00$contentSection$CompanyDetailsControl$txtPostcode'] = 36310;
//        $params['ctl00$contentSection$ContactDetailsControl$txtPhone'] = $business->getPhoneNumber();
//        $params['ctl00$contentSection$ContactDetailsControl$txtEmail'] = $business->getEmail();
//        $params['ctl00$contentSection$ContactDetailsControl$txtWebsite'] = $business->getWebsite();
//        $params['ctl00$contentSection$ContactDetailsControl$txtDescription'] = $business->getDescription();
        $params['ctl00$contentSection$CompanyDetailsControl$txtBusinessName'] = $business->getName();
        $params['ctl00$contentSection$CompanyDetailsControl$txtStreetAddress'] = 'San Telmo, 14 Meyers Pl, Melbourne Victoria, Australia';
        $params['ctl00$contentSection$CompanyDetailsControl$txtAddress2'] = '';
        $params['ctl00$contentSection$CompanyDetailsControl$txtAddress3'] = '';
        $params['ctl00$contentSection$CompanyDetailsControl$txtSuburb'] = 'Hackett';
        $params['ctl00$contentSection$CompanyDetailsControl$cboState'] = 'AL';
        $params['ctl00$contentSection$CompanyDetailsControl$txtPostcode'] = 36310;
        $params['ctl00$contentSection$ContactDetailsControl$txtPhone'] = $business->getPhoneNumber();
        $params['ctl00$contentSection$ContactDetailsControl$txtEmail'] = $business->getEmail();
        $params['ctl00$contentSection$ContactDetailsControl$txtWebsite'] = $business->getWebsite();
        $params['ctl00$contentSection$ContactDetailsControl$txtDescription'] = $business->getDescription();
        $params['ctl00$contentSection$ContactDetailsControl$txtKeywords'] = '';
        $params['ctl00$contentSection$ContactDetailsControl$KeywordsCsv'] = 'new';
        $params['ctl00$contentSection$LoginPasswordControl$txtFirstName'] = $data['hotfrog']['firstName'];
        $params['ctl00$contentSection$LoginPasswordControl$txtLastName'] = $data['hotfrog']['Surname'];
        $params['ctl00$contentSection$LoginPasswordControl$JobTitle$ddlJobTitle'] = 'BusinessOwnerSeniorManager';
        $params['ctl00$contentSection$LoginPasswordControl$JobTitle$txtJobTitleAdHoc'] = 'other';
        $params['ctl00$contentSection$LoginPasswordControl$txtAdminEmail'] = $data['hotfrog']['email'];
        $params['ctl00$contentSection$LoginPasswordControl$txtPassword'] = $data['hotfrog']['userPassword'];
        $params['ctl00$contentSection$LoginPasswordControl$txtConfirmPassword'] = $data['hotfrog']['userPassword'];
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl01$optinCheckBox'] = 'on';
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl01$optinTypeHidden'] = 1;
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl01$optinTypeTranslationKey'] = 'subscribeToHotFrogOffers';
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl02$optinCheckBox'] = 'on';
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl02$optinTypeHidden'] = 2;
        $params['ctl00$contentSection$UserSubsription$subscriptionList$ctl02$optinTypeTranslationKey'] = 'subscribeHotFrogNewsletters';
        $params['LBD_VCT_addyourbusinesssingle_ctl00_contentsection_captchamanager_ctl00_captcha'] = $data['hotfrog']['captcha-hash'];
        $params['ctl00$contentSection$CaptchaManager$ctl00$txtCaptcha'] = strtolower($data['hotfrog']['captcha']);
        $params['ctl00$hotFrogFooter$hotfrogSearch$txtWhat'] = '';
        $params['ctl00$hotFrogFooter$hotfrogSearch$txtWhere'] = '';
        $params['ctl00$HiddenSocialUID'] = '';
        $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = 0;
        if ($this->curl->httpStatusCode !== 302) {
            throw new OAuthCompanyException($this->message);
        }
        $this->curl->post('https://www.hotfrog.com/AddYourBusinessSingle.aspx', $params);
        print($this->curl->response); die;
    }

    public function getCaptcha()
    {
        $this->curl->get('https://www.hotfrog.com/AddYourBusinessSingle.aspx');
        $this->session->set('coocie_hotfrog', $this->curl->getResponseCookies());
        $dom = new DomDocument();
        $dom->loadHTMLFile('https://www.hotfrog.com/AddYourBusinessSingle.aspx');
        $captchaImage = 'https://www.hotfrog.com/'.$dom->getElementById('addyourbusinesssingle_ctl00_contentsection_captchamanager_ctl00_captcha_CaptchaImage')->getAttribute('src');
        $captchaText = $dom->getElementById('LBD_VCT_addyourbusinesssingle_ctl00_contentsection_captchamanager_ctl00_captcha')->getAttribute('value');
        return [$captchaImage, $captchaText];
    }

    public function getDataParses($url)
    {
        $this->curl->get($url);
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($this->curl->response);

        $name = $this->getElementsByClass($dom, 'company-heading')->item(0)->nodeValue;
        $streetAddress = $this->getElementsByClass($dom, 'data-address1')->item(0)->nodeValue;
        $city = $this->getElementsByClass($dom, 'data-city')->item(0)->nodeValue;
        $state= $this->getElementsByClass($dom, 'data-state')->item(0)->nodeValue;
        $postcode= $this->getElementsByClass($dom, 'data-postcode')->item(0)->nodeValue;
        $phone = $this->getElementsByClass($dom, 'phone-type-main')->item(0)->nodeValue;
        return [$streetAddress.', '.$city.', '.$state.' '.$postcode, $phone, $name];
    }

    public function searchBusiness(BusinessInfo $business, $account = null)
    {
        $searchObject = new \StdClass();
        $searchObject->status = self::STATUS_FALSE;
        $searchObject->name = null;
        $searchObject->address = null;
        $searchObject->phone = null;
        $searchObject->url = null;
        $url = $this->getSearchUrl($business->getName(), self::NAME_FOR_SEARCH);
        if(null !== $url){
            $data = $this->getDataParses($url);
            $searchObject->status = self::STATUS_TRUE;
            $searchObject->name = $data[0];
            $searchObject->address = $data[1];
            $searchObject->phone = $data[2];
            $searchObject->url = $url;
        }

        return $searchObject;
    }
}