<?php

namespace Kunstmaan\Rest\RedirectBundle\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Kunstmaan\RedirectBundle\Entity\Redirect;
use Swagger\Annotations as SWG;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\FormBundle\Entity\FormSubmission;
use FOS\RestBundle\Controller\Annotations\View;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use FOS\RestBundle\Controller\Annotations\QueryParam;

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
     * @SWG\Get(
     *     path="/api/redirects",
     *     description="Get all redirects",
     *     operationId="getRedirects",
     *     produces={"application/json"},
     *     tags={"redirect"},
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
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         type="integer",
     *         description="The current page",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         type="integer",
     *         description="Amount of results (default 20)",
     *         required=false,
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
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     *
     * @Rest\Get("/redirects")
     * @View(statusCode=200)
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