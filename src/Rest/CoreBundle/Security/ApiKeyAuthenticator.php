<?php

namespace Kunstmaan\Rest\CoreBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    const KUMA_DEAULT_API_KEY = 'X-Api-Key';

    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $userClass;

    /** @var string */
    private $authenticationHeader;

    /**
     * ApiKeyAuthenticator constructor.
     *
     * @param EntityManagerInterface $em
     * @param string $userClass
     * @param string $authenticationHeader
     */
    public function __construct(EntityManagerInterface $em, string $userClass, string $authenticationHeader)
    {
        $this->em = $em;
        $this->userClass = $userClass;
        $this->authenticationHeader = $authenticationHeader;
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        if ($request->headers->has(self::KUMA_DEAULT_API_KEY)) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getCredentials(Request $request)
    {
        if (!$key = $request->headers->get($this->authenticationHeader)) {
            $key = null;
        }

        return array(
            'key' => $key,
        );
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['key'];

        if (null === $apiKey) {
            return null;
        }

        $userRepo = $this->em->getRepository($this->userClass);

        return $userRepo->findOneBy(['apiKey' => $apiKey]);
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => 'Authentication based on api key failed',
        );

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param Request                      $request
     * @param AuthenticationException|null $authException
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required',
        );

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}