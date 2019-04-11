<?php

namespace Kunstmaan\Rest\ConfigBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\FormBundle\Entity\FormSubmission;
use FOS\RestBundle\Controller\Annotations\View;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;

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
     * @SWG\Get(
     *     path="/api/config/{internalName}",
     *     description="Get config by internal name",
     *     operationId="getConfig",
     *     produces={"application/json"},
     *     tags={"config"},
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/singleConfig")
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch media",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @Rest\Get("/config/{internalName}")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
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
                $config = $repo->findOneBy([]);

                return $config;
            }
        }

        return null;
    }
}