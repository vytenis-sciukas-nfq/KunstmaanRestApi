<?php

namespace Kunstmaan\Rest\ConfigBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use Kunstmaan\ConfigBundle\Entity\AbstractConfig;
use Kunstmaan\FormBundle\Entity\FormSubmission;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 */
class ConfigController extends AbstractApiController
{
    use ControllerTrait;

    /** @var EntityManagerInterface */
    private $doctrine;

    /** @var array */
    private $configuration;

    public function __construct(EntityManagerInterface $doctrine, array $configuration)
    {
        $this->doctrine = $doctrine;
        $this->configuration = $configuration;
    }

    /**
     * Retrieve form submissions paginated
     *
     * @OA\Get(
     *     path="/api/config/{internalName}",
     *     description="Get config by internal name",
     *     operationId="getConfig",
     *     tags={"config"},
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/definitions/singleConfig")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch media",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @Rest\Get("/config/{internalName}")
     * @View(statusCode=200)
     *
     * @return AbstractConfig|Response
     */
    public function getConfigAction(string $internalName)
    {
        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(FormSubmission::class);

        $result = $repository->findAll();

        foreach ($this->configuration['entities'] as $class) {
            $entity = new $class();

            if ($entity->getInternalName() == $internalName) {
                $repo = $this->doctrine->getRepository($class);
                /** @var AbstractConfig $config */
                $config = $repo->findOneBy([]);

                return $config;
            }
        }

        return new NotFoundHttpException("not found");
    }
}
