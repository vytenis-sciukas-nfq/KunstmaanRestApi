<?php

namespace Kunstmaan\Rest\UserBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\AdminBundle\Entity\Role;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 */
class RoleController extends AbstractApiController
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
     * Retrieve Roles paginated
     *
     * @OA\Get(
     *     path="/api/role",
     *     description="Get all roles",
     *     operationId="getRoles",
     *     produces={"application/json"},
     *     tags={"role"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         type="integer",
     *         description="The current page",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         type="integer",
     *         description="Amount of results (default 20)",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/definitions/RoleList")
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
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     *
     * @Rest\Get("/role")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllRolesAction(ParamFetcherInterface $paramFetcher)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Role::class);

        $result = $repository->findAll();

        return $this->getPaginator()->getPaginatedArrayResult($result, $page, $limit);
    }

    /**
     * deletes Role
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Delete(
     *     path="/api/role/{id}",
     *     description="deletes a Role",
     *     operationId="deleteRole",
     *     produces={"application/json"},
     *     tags={"role"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the role",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @Rest\Delete("/role/{id}", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function deleteRoleAction(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $manager = $this->doctrine->getManager();
        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Role::class);

        $manager->remove($repository->find($id));
        $manager->flush();
    }

    /**
     * create a Role
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Post(
     *     path="/api/role",
     *     description="create a Role",
     *     operationId="createRole",
     *     produces={"application/json"},
     *     tags={"role"},
     *     @OA\Parameter(
     *         name="role",
     *         in="body",
     *         type="object",
     *         @OA\JsonContent(ref="#/definitions/Role"),
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @ParamConverter(
     *     name="role",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\AdminBundle\Entity\Role"
     * )
     *
     * @Rest\Post("/role")
     *
     * @param Role $role
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return null
     * @throws \Exception
     */
    public function postRoleAction(Role $role, ConstraintViolationListInterface $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $this->doctrine->getManager()->persist($role);
        $this->doctrine->getManager()->flush();
    }

    /**
     * updates a Role
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Put(
     *     path="/api/role/{id}",
     *     description="update a Role",
     *     operationId="updateRole",
     *     produces={"application/json"},
     *     tags={"role"},
     *     @OA\Parameter(
     *         name="role",
     *         in="body",
     *         type="object",
     *         @OA\JsonContent(ref="#/definitions/Role"),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @ParamConverter(
     *     name="role",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\AdminBundle\Entity\Role"
     * )
     *
     * @Rest\Put("/role/{id}", requirements={"id": "\d+"})
     *
     * @param Role $role
     * @param ConstraintViolationListInterface $validationErrors
     * @param int                              $id
     *
     * @return null
     * @throws \Exception
     */
    public function updateRoleAction(Role $role, ConstraintViolationListInterface $validationErrors, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Role::class);

        /** @var Role $originalRole */
        $originalRole = $repository->find($id);

        if(!empty($role->getRole())) {
            $originalRole->setRole($role->getRole());
        }
        $this->doctrine->getManager()->flush();
    }
}
