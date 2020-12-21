<?php

namespace Kunstmaan\Rest\UserBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\AdminBundle\Entity\BaseUser;
use Kunstmaan\AdminBundle\Repository\UserRepository;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Kunstmaan\Rest\CoreBundle\Entity\RestUser;
use Kunstmaan\Rest\CoreBundle\Helper\GenerateApiKeyFunctionTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Kunstmaan\Rest\UserBundle\Model\UserModel;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UserController extends AbstractApiController
{
    use ControllerTrait;
    use GenerateApiKeyFunctionTrait;

    /** @var Registry */
    private $doctrine;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var UserManager */
    private $userManager;

    /**
     * MediaController constructor.
     * @param Registry     $doctrine
     * @param TokenStorage $tokenStorage
     * @param UserManager  $userManager
     */
    public function __construct(Registry $doctrine, TokenStorage $tokenStorage, UserManager $userManager)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
    }

    /**
     * Retrieve Users paginated
     *
     * @OA\Get(
     *     path="/api/user",
     *     description="Get all users",
     *     operationId="getUsers",
     *     tags={"user"},
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
     *         name="groupId",
     *         in="query",
     *         type="integer",
     *         description="The id of the group of the user",
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
     *         @OA\JsonContent(ref="#/definitions/UserList")
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

    /**
     * updates a User
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Put(
     *     path="/api/user/{id}",
     *     description="update a User",
     *     operationId="updateUser",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="userModel",
     *         in="body",
     *         type="object",
     *         @OA\JsonContent(ref="#/definitions/PutUser"),
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
     *     name="userModel",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\Rest\UserBundle\Model\UserModel",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "update"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "update"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Put("/user/{id}", requirements={"id": "\d+"})
     *
     * @param UserModel                        $userModel
     * @param ConstraintViolationListInterface $validationErrors
     * @param int                              $id
     *
     * @return null
     * @throws \Exception
     */
    public function updateUserAction(UserModel $userModel, ConstraintViolationListInterface $validationErrors, int $id)
    {
        /** @var RestUser $me */
        $me = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $me || !$me instanceof BaseUser || $me->getId() !== $id) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        }

        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }
        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(RestUser::class);

        /** @var BaseUser $user */
        $user = $repository->find($id);

        if (!empty($userModel->getGroups())) {
            foreach ($user->getGroups() as $group) {
                $user->removeGroup($group);
            }

            foreach ($userModel->getGroups() as $group) {
                $user->addGroup($group);
            }
        }

        if (!empty($userModel->getPassword()) && !empty($userModel->getPasswordConfirm())) {
            if ($userModel->getPassword() !== $userModel->getPasswordConfirm()) {
                return new \FOS\RestBundle\View\View('Password and Password confirmation should be the same', Response::HTTP_BAD_REQUEST);
            }
            $user->setPasswordChanged(true);
            $user->setPlainPassword($userModel->getPassword());
        }

        if (!empty($userModel->getEmail())) {
            $user->setEmail($userModel->getEmail());
        }

        if (!empty($userModel->getUsername())) {
            $user->setUsername($userModel->getUsername());
        }
        if (!empty($userModel->getAdminLocale())) {
            $user->setAdminLocale($userModel->getAdminLocale());
        }

        if (null !== $userModel->isEnabled()) {
            $user->setEnabled($userModel->isEnabled());
        }

        $this->userManager->updateUser($user, true);
    }

    /**
     * create a User
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Post(
     *     path="/api/user",
     *     description="create a User",
     *     operationId="createUser",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="userModel",
     *         in="body",
     *         type="object",
     *         @OA\JsonContent(ref="#/definitions/PostUser"),
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
     *     name="userModel",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\Rest\UserBundle\Model\UserModel",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "create"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "create"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Post("/user")
     *
     * @param UserModel                        $userModel
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return null
     * @throws \Exception
     */
    public function postUserAction(UserModel $userModel, ConstraintViolationListInterface $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        if ($userModel->getPassword() !== $userModel->getPasswordConfirm()) {
            return new \FOS\RestBundle\View\View('Password and Password confirmation should be the same', Response::HTTP_BAD_REQUEST);
        }

        /** @var BaseUser $user */
        $user = new RestUser();

        foreach ($userModel->getGroups() as $group) {
            $user->addGroup($group);
        }

        $user->setPlainPassword($userModel->getPassword());
        $user->setEmail($userModel->getEmail());
        $user->setUsername($userModel->getUsername());
        $user->setAdminLocale($userModel->getAdminLocale());
        $user->setEnabled(true);

        $this->userManager->updateUser($user, true);
    }

    /**
     * deletes User
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     description="deletes a User",
     *     operationId="deleteUser",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user",
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
     * @Rest\Delete("/user/{id}", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function deleteUserAction(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $manager = $this->doctrine->getManager();
        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(RestUser::class);

        $manager->remove($repository->find($id));
        $manager->flush();
    }

    /**
     * toggle User
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Put(
     *     path="/api/user/{id}/toggle-enabled",
     *     description="toggle a Users enabled state",
     *     operationId="toggleEnabledUser",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user",
     *         required=true,
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
     * @Rest\Put("/user/{id}/toggle-enabled", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function toggleEnableUserAction(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $manager = $this->doctrine->getManager();
        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(RestUser::class);
        /** @var BaseUser $user */
        $user = $repository->find($id);
        $user->setEnabled(!$user->isEnabled());
        $manager->flush();
    }

    /**
     * refresh api key
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Put(
     *     path="/api/user/{id}/refresh-key",
     *     description="refresh api key",
     *     operationId="refreshApiKey",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The id of the user",
     *         required=true,
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
     * @Rest\Put("/user/{id}/refresh-key", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function refreshApiKeyAction(int $id)
    {
        /** @var RestUser $me */
        $me = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (null === $me || !$me instanceof BaseUser || $me->getId() !== $id) {
            $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        }

        $manager = $this->doctrine->getManager();
        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(RestUser::class);

        /** @var RestUser $user */
        $user = $repository->find($id);

        $key = $this->generateApiKey();
        $user->setApiKey($key);
        $manager->flush();

        return ['key' => $key];
    }

}
