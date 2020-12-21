<?php

namespace Kunstmaan\Rest\CoreBundle\Controller;

use Kunstmaan\Rest\CoreBundle\Security\ApiKeyAuthenticator;
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class DocumentationController
{
    private $generatorLocator;

    private $apiKeyHeader;

    /**
     * @param ContainerInterface $generatorLocator
     * @param string             $apiKeyHeader
     */
    public function __construct($generatorLocator, $apiKeyHeader)
    {
        if (!$generatorLocator instanceof ContainerInterface) {
            if (!$generatorLocator instanceof ApiDocGenerator) {
                throw new \InvalidArgumentException(sprintf('Providing an instance of "%s" to "%s" is not supported.', get_class($generatorLocator), __METHOD__));
            }

            @trigger_error(sprintf('Providing an instance of "%s" to "%s()" is deprecated since version 3.1. Provide it an instance of "%s" instead.', ApiDocGenerator::class, __METHOD__, ContainerInterface::class), E_USER_DEPRECATED);
            $generatorLocator = new ServiceLocator(['default' => function () use ($generatorLocator): ApiDocGenerator {
                return $generatorLocator;
            }]);
        }

        $this->generatorLocator = $generatorLocator;
        $this->apiKeyHeader = $apiKeyHeader;
    }

    public function __invoke(Request $request, $area = 'default')
    {
        if (!$this->generatorLocator->has($area)) {
            throw new BadRequestHttpException(sprintf('Area "%s" is not supported.', $area));
        }

        $spec = $this->generatorLocator->get($area)->generate()->toArray();
        if ('' !== $request->getBaseUrl()) {
            $spec['basePath'] = $request->getBaseUrl();
        }

        $json = json_encode($spec);
        $json = preg_replace('/'.ApiKeyAuthenticator::KUMA_DEAULT_API_KEY.'/', $this->apiKeyHeader, $json);

        return new JsonResponse($json, 200, [], true);
    }
}
