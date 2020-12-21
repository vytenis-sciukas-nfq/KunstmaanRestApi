<?php

namespace Kunstmaan\Rest\FormBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\FormBundle\Entity\FormSubmission;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 */
class FormSubmissionController extends AbstractApiController
{
    use ControllerTrait;

    /** @var Registry */
    private $doctrine;

    /**
     * MediaController constructor.
     * @param Registry     $doctrine
     * @param TokenStorage $tokenStorage
     * @param UserManager  $userManager
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Retrieve form submissions paginated
     *
     * @OA\Get(
     *     path="/api/form-submission",
     *     description="Get all form-submissions",
     *     operationId="getFormSubmissions",
     *     tags={"form-submission"},
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
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/FormSubmissionList")
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
     *
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     *
     * @Rest\Get("/form-submission")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllFormSubmissionsAction(ParamFetcherInterface $paramFetcher)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(FormSubmission::class);

        $result = $repository->findAll();

        return $this->getPaginator()->getPaginatedArrayResult($result, $page, $limit);
    }
}
