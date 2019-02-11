<?php

namespace Kunstmaan\Rest\UserBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\AdminBundle\Entity\BaseUser;
use Kunstmaan\AdminBundle\Repository\UserRepository;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Kunstmaan\Rest\CoreBundle\Entity\RestUser;
use Swagger\Annotations as SWG;

/**
 * Class UserController
 *
 */
class UserController extends AbstractApiController
{
    use ControllerTrait;

    /** @var Registry */
    private $doctrine;

    /**
     * MediaController constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Retrieve User paginated
     *
     * @SWG\Get(
     *     path="/api/user",
     *     description="Get all users",
     *     operationId="getUsers",
     *     produces={"application/json"},
     *     tags={"user"},
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
     *     @SWG\Parameter(
     *         name="groupId",
     *         in="query",
     *         type="integer",
     *         description="The email of user",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/UserList")
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
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     * @QueryParam(name="groupId", nullable=true, description="the groupId to search in", requirements="\d+", strict=true)
     *
     * @Rest\Get("/user")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllUserAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $groupId = $paramFetcher->get('groupId');

        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(RestUser::class);

        $result = $repository->findAll();
        if ($groupId) {
            /** @var BaseUser $user */
            foreach ($result as $user) {
                if (\in_array($groupId, $user->getGroupIds(), false)) {
                    $filteredResults[] = $user;
                }
            }
        }

        return $this->getPaginator()->getPaginatedArrayResult($result, $page, $limit);
    }
}
