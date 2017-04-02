<?php

namespace AppBundle\Security;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GithubAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $router;

    public function __construct($clientId, $clientSecret, Router $router)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->router = $router;
    }


    public function createToken(Request $request, $providerKey)
    {
        // Récupérer l'accessToken dans le header de la request
        $bearer = $request->headers->get('Authorization');
        $accessToken = substr($bearer, 7);

        return new PreAuthenticatedToken(
            'anon.',
            $accessToken,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $accessToken = $token->getCredentials();

        $user = $userProvider->loadUserByUsername($accessToken);

        return new PreAuthenticatedToken(
            $user,
            $accessToken,
            $providerKey,
            ['ROLE_USER']
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response("Authentication Failed :(", 403);
    }
}