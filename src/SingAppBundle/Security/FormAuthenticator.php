<?php

namespace SingAppBundle\Security;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use SingAppBundle\Security\Authentication\AppUserProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FormAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /** @var UserPasswordEncoder $encoder */
    private $encoder;

    /**
     * Default message for authentication failure.
     *
     * @var string
     */
    private $failMessage = 'The username or password you entered doesn\'t belong to an account. Please check your credentials and try again.';

    /**
     * Creates a new instance of FormAuthenticator.
     */
    public function __construct(RouterInterface $router, UserPasswordEncoder $encoder)
    {
        $this->router = $router;

        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if ('/authorization' != $request->getPathInfo() || !$request->isMethod('POST')) {
            return;
        }

        return array(
            'username' => $request->request->get('appbundle_auth')['username'],
            'password' => $request->request->get('appbundle_auth')['password'],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$userProvider instanceof AppUserProvider) {
            return;
        }

        try {
            return $userProvider->loadUserByUsername($credentials);
        } catch (UsernameNotFoundException $e) {
            throw new CustomUserMessageAuthenticationException($this->failMessage);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($this->encoder->isPasswordValid($user, $credentials['password'])) {
            return true;
        }

        throw new CustomUserMessageAuthenticationException($this->failMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return new RedirectResponse($this->router->generate('index'));
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        $url = $this->router->generate('authorization');

        return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('authorization');

        return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
