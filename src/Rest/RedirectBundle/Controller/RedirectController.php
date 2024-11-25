<?php

namespace Kunstmaan\Rest\RedirectBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\RedirectBundle\Entity\Redirect;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;

class RedirectController extends AbstractApiController
{
    use ControllerTrait;

    /** @var EntityManagerInterface */
    private $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Retrieve form submissions paginated
     *
     * @OA\Get(
     *     path="/api/redirects",
     *     description="Get all redirects",
     *     operationId="getRedirects",
     *     tags={"redirect"},
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/listRedirect")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The current page",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Amount of results (default 20)",
     *         required=false,
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch media",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     *
     * @Rest\Get("/redirects")
     * @View(statusCode=200, serializerGroups={"Default"})
     *
     * @return PaginatedRepresentation
     */
    public function getRedirectsAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Redirect::class);
        $result = $repository->findAll();

        return $this->getPaginator()->getPaginatedArrayResult($result, $page, $limit);
    }
}
