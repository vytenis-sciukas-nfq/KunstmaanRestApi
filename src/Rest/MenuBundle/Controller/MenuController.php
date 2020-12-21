<?php

namespace Kunstmaan\Rest\MenuBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\UserBundle\Doctrine\UserManager;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\MenuBundle\Entity\Menu;
use Kunstmaan\MenuBundle\Entity\MenuItem;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 */
class MenuController extends AbstractApiController
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
     * Retrieve menu paginated
     *
     * @OA\Get(
     *     path="/api/menu",
     *     description="Get all menu",
     *     operationId="getMenus",
     *     tags={"menu"},
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
     *         @OA\JsonContent(ref="#/definitions/MenuList")
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
     * @Rest\Get("/menu")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllMenuAction(ParamFetcherInterface $paramFetcher)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Menu::class);

        $result = $repository->findAll();

        return $this->getPaginator()->getPaginatedArrayResult($result, $page, $limit);
    }

    /**
     * Retrieve menu items paginated
     *
     * @OA\Get(
     *     path="/api/menu/{id}/items",
     *     description="Get all menu items for menu",
     *     operationId="getMenuItems",
     *     tags={"menu"},
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
     *         name="id",
     *         in="path",
     *         description="The id of the menu",
     *         required=true,
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
     *         @OA\JsonContent(ref="#/definitions/MenuItemList")
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
     * @Rest\Get("/menu/{id}/items")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllMenuItemAction(ParamFetcherInterface $paramFetcher, int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Menu::class);

        /** @var Menu $result */
        $result = $repository->find($id);
        $result = $result->getItems();

        return $this->getPaginator()->getPaginatedArrayResult($result->toArray(), $page, $limit);
    }

    /**
     * deletes MenuItem
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Delete(
     *     path="/api/menu-item/{id}",
     *     description="deletes a menu item",
     *     operationId="deleteMenuItem",
     *     tags={"menu"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the menu item",
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
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @Rest\Delete("/menu-item/{id}", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function deleteMenuItemAction(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(MenuItem::class);
        $item = $repository->find($id);
        $this->doctrine->getManager()->remove($item);
        $this->doctrine->getManager()->flush();
    }

    /**
     * deletes Menu
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Delete(
     *     path="/api/menu/{id}",
     *     description="deletes a menu",
     *     operationId="deleteMenu",
     *     tags={"menu"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the menu",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
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
     * @Rest\Delete("/menu/{id}", requirements={"id": "\d+"})
     *
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function deleteMenuAction(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var ObjectRepository $repository */
        $repository = $this->doctrine->getRepository(Menu::class);
        /** @var Menu $menu */
        $menu = $repository->find($id);
        foreach($menu->getItems() as $item) {
            $this->doctrine->getManager()->remove($item);
        }
        $this->doctrine->getManager()->remove($menu);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Creates a new Menu
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Post(
     *     path="/api/menu",
     *     description="Creates a Menu",
     *     operationId="postMenu",
     *     tags={"menu"},
     *     @OA\Parameter(
     *         name="menu",
     *         in="body",
     *         @OA\JsonContent(ref="#/definitions/PostMenu"),
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
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
     *     name="menu",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\MenuBundle\Entity\Menu",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Post("/menu")
     *
     * @param Menu $menu
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @return null
     * @throws \Exception
     */
    public function createMenuAction(Menu $menu, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $this->doctrine->getManager()->persist($menu);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Creates a new Menu item
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Post(
     *     path="/api/menu/{id}/add",
     *     description="add a new menu item to a menu",
     *     operationId="addMenuItem",
     *     tags={"menu"},
     *     @OA\Parameter(
     *         name="menuItem",
     *         in="body",
     *         @OA\JsonContent(ref="#/definitions/PostMenuItem"),
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the menu",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
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
     *     name="menuItem",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\MenuBundle\Entity\MenuItem",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Post("/menu/{id}/add")
     *
     * @param MenuItem $menuItem
     * @param ConstraintViolationListInterface $validationErrors
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function addMenuItemAction(MenuItem $menuItem, ConstraintViolationListInterface $validationErrors, int $id)
    {
        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }
        $manager = $this->doctrine->getManager();


       /** @var Menu $originalMenu */
        $originalMenu = $manager->find(Menu::class, $id);
        $menuItem->setId(null);
        $menuItem->setMenu($originalMenu);
        $originalMenu->addItem($menuItem);
        $manager->persist($menuItem);
        $manager->flush();
    }

    /**
     * Update a menu
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Put(
     *     path="/api/menu/{id}",
     *     description="updates a Menu",
     *     operationId="putMenu",
     *     tags={"menu"},
     *     @OA\Parameter(
     *         name="menu",
     *         in="body",
     *         @OA\JsonContent(ref="#/definitions/PostMenu"),
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the menu",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
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
     *     name="menu",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\MenuBundle\Entity\Menu",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Put("/menu/{id}")
     *
     * @param Menu $menu
     * @param ConstraintViolationListInterface $validationErrors
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function updateMenuAction(Menu $menu, ConstraintViolationListInterface $validationErrors, int $id)
    {
        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }
        $manager = $this->doctrine->getManager();
        /** @var Menu $originalMenu */
        $originalMenu = $manager->find(Menu::class, $id);
        if($menu->getName()) {
            $originalMenu->setName($menu->getName());
        }
        if($menu->getLocale()) {
            $originalMenu->setLocale($menu->getLocale());
        }
        $manager->flush();
    }

    /**
     * Update a menuItem
     *
     * @View(
     *     statusCode=202
     * )
     *
     * @OA\Put(
     *     path="/api/menu-item/{id}",
     *     description="updates a MenuItem",
     *     operationId="putMenuItem",
     *     tags={"menu"},
     *     @OA\Parameter(
     *         name="menuItem",
     *         in="body",
     *         @OA\JsonContent(ref="#/definitions/PutMenuItem"),
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The id of the menuItem",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Returned when successful",
     *     ),
     *     @OA\Parameter(
     *         name="X-Api-Key",
     *         in="header",
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
     *     name="menuItem",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\MenuBundle\Entity\MenuItem",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "Default",
     *                  "list"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Put("/menu-item/{id}")
     *
     * @param MenuItem $menuItem
     * @param ConstraintViolationListInterface $validationErrors
     * @param int $id
     *
     * @return null
     * @throws \Exception
     */
    public function updateMenuItemAction(MenuItem $menuItem, ConstraintViolationListInterface $validationErrors, int $id)
    {
        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }
        $manager = $this->doctrine->getManager();
        /** @var MenuItem $originalMenuItem */
        $originalMenuItem = $manager->find(MenuItem::class, $id);
        if($menuItem->getUrl()) {
            $originalMenuItem->setUrl($menuItem->getUrl());
        }
        if($menuItem->getType()) {
            $originalMenuItem->setType($menuItem->getType());
        }
        if($menuItem->getTitle()) {
            $originalMenuItem->setTitle($menuItem->getTitle());
        }
        if($menuItem->getLft()) {
            $originalMenuItem->setLft($menuItem->getLft());
        }
        if($menuItem->getRgt()) {
            $originalMenuItem->setRgt($menuItem->getRgt());
        }
        if($menuItem->getLvl()) {
            $originalMenuItem->setLvl($menuItem->getLvl());
        }
        if($menuItem->getUrl()) {
            $originalMenuItem->setUrl($menuItem->getUrl());
        }
        if($menuItem->getNodeTranslation()) {
           $originalMenuItem->setNodeTranslation($menuItem->getNodeTranslation());
        }

        $manager->flush();
    }
}
