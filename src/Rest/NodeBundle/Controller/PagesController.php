<?php

/*
 * This file is part of the KunstmaanBundlesCMS package.
 *
 * (c) Kunstmaan <https://github.com/Kunstmaan/KunstmaanBundlesCMS/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kunstmaan\Rest\NodeBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcher;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Helper\NodeHelper;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Kunstmaan\Rest\CoreBundle\Helper\DataTransformerTrait;
use Kunstmaan\Rest\CoreBundle\Service\DataTransformerService;
use Kunstmaan\Rest\NodeBundle\Model\ApiPage;
use Kunstmaan\Rest\NodeBundle\Service\Helper\PagePartHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class PagesController extends AbstractApiController
{
    use ControllerTrait;
    use DataTransformerTrait;

    /** @var EntityManagerInterface */
    private $em;

    /** @var NodeHelper */
    private $nodeHelper;

    /** @var PagePartHelper */
    private $pagePartHelper;

    /**
     * PagesController constructor.
     *
     * @param EntityManagerInterface $em
     * @param DataTransformerService $dataTransformer
     * @param NodeHelper             $nodeHelper
     * @param PagePartHelper         $pagePartHelper
     */
    public function __construct(
        EntityManagerInterface $em,
        DataTransformerService $dataTransformer,
        NodeHelper $nodeHelper,
        PagePartHelper $pagePartHelper
    ) {
        $this->em = $em;
        $this->dataTransformer = $dataTransformer;
        $this->nodeHelper = $nodeHelper;
        $this->pagePartHelper = $pagePartHelper;
    }

    /**
     * Retrieve nodes paginated
     *
     * @OA\Get(
     *     path="/api/public/pages",
     *     description="Get a pages of a certain type",
     *     operationId="getPages",
     *     tags={"pages"},
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
     *         name="type",
     *         in="query",
     *         description="The FQCN of the page",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="The language of your content",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="internalName",
     *         in="query",
     *         description="The internal name of the page",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="nodeId",
     *         in="query",
     *         description="Node id",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="online",
     *         in="query",
     *         description="Include only online nodes",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="versionType",
     *         in="query",
     *         description="VersionType (public or draft)",
     *         required=false,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/ApiPage")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @OA\JsonContent(ref="#/components/schemas/Node")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/Node")
     *     )
     * )
     *
     * @Rest\Get("/public/pages")
     * @View(statusCode=200, serializerGroups={"Default"})
     *
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page", strict=true)
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results", strict=true)
     * @QueryParam(name="type", nullable=true, requirements="[\d\w\\]+", description="fqcn of the page", strict=true)
     * @QueryParam(name="locale", nullable=true, requirements="[A-Za-z_-]+", description="locale", strict=true)
     * @QueryParam(name="internalName", nullable=true, requirements="[\w_-]+", description="Internal name of the page", strict=true)
     * @QueryParam(name="nodeId", nullable=true, requirements="\d+", description="Node id", strict=true)
     * @QueryParam(name="online", nullable=true, allowBlank=true, default="true", requirements="(true|false)", description="Online node translations", strict=true)
     * @QueryParam(name="versionType", nullable=true, allowBlank=true, requirements="(public|draft)", description="Version type (public or draft)", strict=true)
     *
     * @param ParamFetcher $paramFetcher
     * @return PaginatedRepresentation
     */
    public function getPagesAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $type = $paramFetcher->get('type');
        $locale = $paramFetcher->get('locale');
        $internalName = $paramFetcher->get('internalName');
        $nodeId = $paramFetcher->get('nodeId');
        $online = $paramFetcher->get('online');
        $versionType = $paramFetcher->get('versionType');

        $qb = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')->getNodeTranslationsQueryBuilder($locale);

        if ($type) {
            $qb->andWhere('v.refEntityName = :refEntityName')
                ->setParameter('refEntityName', $type);
        }
        if ($internalName) {
            $qb->andWhere('n.internalName = :internalName')
                ->setParameter('internalName', $internalName);
        }
        if ($nodeId > 0) {
            $qb->andWhere('n.id = :nodeId')
                ->setParameter('nodeId', $nodeId);
        }
        if ($online === 'false') {
            $qb->andWhere('nt.online = false');
        } else {
            $qb->andWhere('nt.online = true');
        }
        if ('draft' === $versionType) {
            $qb->innerJoin('nt.nodeVersions', 'nv_draft', 'WITH', 'nt.id = nv_draft.nodeTranslation AND nv_draft.type = \'draft\'')
                ->leftJoin('nt.nodeVersions', 'nv_best', 'WITH', 'nv_draft.nodeTranslation = nv_best.nodeTranslation AND nv_best.created > nv_draft.created AND nv_best.type = \'draft\'')
                ->andWhere('(nv_best.nodeTranslation IS NULL)')
            ;
        }

        return $this->getPaginator()->getPaginatedQueryBuilderResult($qb, $page, $limit, $this->createTransformerDecorator());
    }

    /**
     * Get a page by node translation ID
     *
     * @OA\Get(
     *     path="/api/public/pages/{id}",
     *     description="Get a page by node translation ID",
     *     operationId="getPublicPage",
     *     tags={"pages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The node translation ID",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/GetApiPage")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     *
     * @Rest\Get("/public/pages/{id}", requirements={"id": "\d+"})
     * @View(statusCode=200, serializerGroups={"Default"})
     *
     * @throws \Exception
     *
     * @param int $id
     * @return ApiPage
     */
    public function getPublicPageAction($id)
    {

        $qb = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')->getOnlineNodeTranslationsQueryBuilder()
            ->andWhere('nt.id = :id')
            ->setParameter('id', $id)
        ;

        $nodeTranslation = $qb->getQuery()->getOneOrNullResult();

        if (!$nodeTranslation instanceof NodeTranslation) {
            throw new NotFoundHttpException();
        }

        return $this->dataTransformer->transform($nodeTranslation);
    }

    /**
     * Get a page by node translation ID
     *
     * @OA\Get(
     *     path="/api/pages/{id}",
     *     description="Get a page by node translation ID",
     *     operationId="getPage",
     *     tags={"pages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The node translation ID",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/GetApiPage")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     *
     * @Rest\Get("/pages/{id}", requirements={"id": "\d+"})
     * @View(statusCode=200, serializerGroups={"Default"})
     *
     * @throws \Exception
     *
     * @param int $id
     * @return ApiPage
     */
    public function getPageAction($id)
    {

        $qb = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')->getNodeTranslationsQueryBuilder()
            ->andWhere('nt.id = :id')
            ->setParameter('id', $id)
        ;

        $nodeTranslation = $qb->getQuery()->getOneOrNullResult();

        if (!$nodeTranslation instanceof NodeTranslation) {
            throw new NotFoundHttpException();
        }

        return $this->dataTransformer->transform($nodeTranslation);
    }

    /**
     * Update a ApiPage
     *
     * @View(
     *     statusCode=204,
     *     serializerGroups={"Default"}
     * )
     *
     * @OA\Put(
     *     path="/api/pages/{id}",
     *     description="Update a ApiPage",
     *     operationId="putApiPage",
     *     tags={"pages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The node translation ID",
     *         required=true,
     *     ),
     *     @OA\RequestBody(
     *         description="apiPage",
     *         @OA\JsonContent(ref="#/components/schemas/ApiPage")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/PutApiPage")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     *
     * @ParamConverter(
     *     name="apiPage",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\Rest\NodeBundle\Model\ApiPage",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "edit"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "edit",
     *                  "Default"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Put("/pages/{id}")
     *
     * @param Request                          $request
     * @param ApiPage                          $apiPage
     * @param integer                          $id
     * @param ConstraintViolationListInterface $validationErrors
     *
     * @throws \Exception
     *
     * @return null
     */
    public function putPagesAction(Request $request, ApiPage $apiPage, $id, ConstraintViolationListInterface $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        if (null === ($nodeTranslation = $apiPage->getNodeTranslation())) {
            $nodeTranslation = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')->find($id);
        }

        $node = $apiPage->getNode();
        $nodeVersion = $nodeTranslation->getPublicNodeVersion();

        list($nodeVersionIsLocked, $nodeVersion) = $this->nodeHelper->createNodeVersion($nodeTranslation, $nodeVersion);

        $page = $nodeVersion->getRef($this->em);
        $isStructureNode = $page->isStructureNode();

        $this->pagePartHelper->updatePageParts($apiPage, $page);
        $this->nodeHelper->updateNode($node, $nodeTranslation, $nodeVersion, $page, $isStructureNode);
    }

    /**
     * Creates a ApiPage
     *
     * @View(
     *     statusCode=204,
     *     serializerGroups={"Default"}
     * )
     *
     *
     * @OA\Post(
     *     path="/api/pages",
     *     description="Creates a ApiPage",
     *     operationId="postApiPage",
     *     tags={"pages"},
     *     @OA\RequestBody(
     *         description="apiPage",
     *         @OA\JsonContent(ref="#/components/schemas/PostApiPage"),
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/ApiPage")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     )
     * )
     *
     * @ParamConverter(
     *     name="apiPage",
     *     converter="fos_rest.request_body",
     *     class="Kunstmaan\Rest\NodeBundle\Model\ApiPage",
     *     options={
     *          "deserializationContext"={
     *              "groups"={
     *                  "create"
     *              }
     *          },
     *          "validator"={
     *              "groups"={
     *                  "create",
     *                  "Default"
     *              }
     *          }
     *     }
     * )
     *
     * @Rest\Post("/pages")
     * @param Request                          $request
     * @param ApiPage                          $apiPage
     * @param ConstraintViolationListInterface $validationErrors
     */
    public function postPagesAction(Request $request, ApiPage $apiPage, ConstraintViolationListInterface $validationErrors)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (count($validationErrors) > 0) {
            return new \FOS\RestBundle\View\View($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        /* @var Node $parentNode */
        $parentNode = $apiPage->getNode()->getParent();
        $locale = $apiPage->getNodeTranslation()->getLang();

        $this->nodeHelper->createNodeTranslation($parentNode);
    }
}
