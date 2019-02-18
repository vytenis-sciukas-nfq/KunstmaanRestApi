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
use Kunstmaan\AdminBundle\Entity\Group;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 */
class GroupController extends AbstractApiController
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
     * Retrieve Groups paginated
     *
     * @SWG\Get(
     *     path="/api/group",
     *     description="Get all groups",
     *     operationId="getGroups",
     *     produces={"application/json"},
     *     tags={"group"},
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
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/GroupList")
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
     *
     * @Rest\Get("/group")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllGroupsAction(ParamFetcherInterface $paramFetcher)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Group::class);

        $result = $repository->findAll();

        return $this->getPaginator()->getPaginatedArrayResult($result, $page, $limit);
    }

    /**
     * deletes Group
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @SWG\Delete(
     *     path="/api/group/{id}",
     *     description="deletes a Group",
     *     operationId="deleteGroup",
     *     produces={"application/json"},
     *     tags={"group"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the group",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @Rest\Delete("/group/{id}", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function deleteGroupAction(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $manager = $this->doctrine->getManager();
        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Group::class);

        $manager->remove($repository->find($id));
        $manager->flush();
    }

    /**
     * create a Group
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @SWG\Post(
     *     path="/api/group",
     *     description="create a Group",
     *     operationId="createGroup",
     *     produces={"application/json"},
     *     tags={"group"},
     *     @SWG\Parameter(
     *         name="group",
     *         in="body",
     *         type="object",
     *         @SWG\Schema(ref="#/definitions/Group"),
     *     ),
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @ParamConverter(
     *     name="group",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\AdminBundle\Entity\Group"
     * )
     *
     * @Rest\Post("/group")
     *
     * @param Group $group
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return null
     * @throws \Exception
     */
    public function postGroupAction(Group $group, ConstraintViolationListInterface $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $this->doctrine->getManager()->persist($group);
        $this->doctrine->getManager()->flush();
    }

    /**
     * updates a Group
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @SWG\Put(
     *     path="/api/group/{id}",
     *     description="update a Group",
     *     operationId="updateGroup",
     *     produces={"application/json"},
     *     tags={"group"},
     *     @SWG\Parameter(
     *         name="group",
     *         in="body",
     *         type="object",
     *         @SWG\Schema(ref="#/definitions/Group"),
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the group",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @ParamConverter(
     *     name="group",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\AdminBundle\Entity\Group"
     * )
     *
     * @Rest\Put("/group/{id}", requirements={"id": "\d+"})
     *
     * @param Group $group
     * @param ConstraintViolationListInterface $validationErrors
     * @param int                              $id
     *
     * @return null
     * @throws \Exception
     */
    public function updateGroupAction(Group $group, ConstraintViolationListInterface $validationErrors, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Group::class);

        /** @var Group $originalGroup */
        $originalGroup = $repository->find($id);

        if(!empty($group->getName())) {
            $originalGroup->setName($group->getName());
        }
        if(!empty($group->getRolesCollection())) {
            $originalGroup->setRolesCollection($group->getRolesCollection());
        }
        $this->doctrine->getManager()->flush();
    }
}