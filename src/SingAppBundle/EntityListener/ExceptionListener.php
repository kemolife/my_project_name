<?php

namespace SingAppBundle\EntityListener;


use Google_Service_Exception;
use JMS\AopBundle\Exception\Exception;
use RuntimeException;
use SingAppBundle\Providers\Exception\OAuthCompanyException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $referer = $event->getRequest()->headers->get('referer');

        $exception = $event->getException();

        if ($exception instanceof OAuthCompanyException || $exception instanceof Exception || $exception instanceof Google_Service_Exception) {
            if (!$referer) {
                $referer = $this->router->generate('index', ['error' => $exception->getMessage()]);
            }else {
                $referer = $referer.'?error='.$exception->getMessage();
            }

            $response = new RedirectResponse($referer);

            $event->setResponse($response);
        }

    }
}