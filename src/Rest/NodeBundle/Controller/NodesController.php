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
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Repository\NodeRepository;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use OpenApi\Annotations as OA;

class NodesController extends AbstractApiController
{
    use ControllerTrait;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * NodesController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retrieve nodes paginated
     *
     * @OA\Get(
     *     path="/api/nodes",
     *     description="Get all nodes",
     *     operationId="getNodes",
     *     tags={"nodes"},
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
     *         name="internalName",
     *         in="query",
     *         description="The internal name of the node",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="Locale",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="hiddenFromNav",
     *         in="query",
     *         description="If true, only nodes hidden from nav will be returned",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *         name="refEntityName",
     *         in="query",
     *         description="Which pages you want to have returned",
     *         required=false,
     *     ),
     *     @OA\Parameter(
     *          name="includeChildren",
     *          in="query",
     *          description="Do you want to include node children?",
     *          required=false,
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedNodeList")
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
     * @QueryParam(name="internalName", nullable=true, description="The internal name of the node", requirements="[\w\d_-]+", strict=true)
     * @QueryParam(name="hiddenFromNav", nullable=true, allowBlank=true, default=null, requirements="(true|false)", description="If true, only nodes hidden from nav will be returned", strict=true)
     * @QueryParam(name="includeChildren", nullable=true, allowBlank=true, default=null, requirements="(true|false)", description="If true, child nodes will be included recursively", strict=true)
     * @QueryParam(name="refEntityName", nullable=true, default=null, description="Which pages you want to have returned")
     * @QueryParam(name="locale", nullable=true, default=null, requirements="[a-zA-Z_-]+", strict=true, description="If you provide a locale, then only nodes with a node translation of this locale will be returned")
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     *
     * @Rest\Get("/nodes")
     * @Rest\View(statusCode=200, serializerGroups={"Default"})
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getNodesAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $internalName = $paramFetcher->get('internalName');
        $hiddenFromNav = $paramFetcher->get('hiddenFromNav');
        $refEntityName = $paramFetcher->get('refEntityName');
        $locale = $paramFetcher->get('locale');
        $includeChildren = $paramFetcher->get('includeChildren');

        /** @var NodeRepository $repository */
        $repository = $this->em->getRepository(Node::class);
        $qb = $repository->createQueryBuilder('n');
        $qb->where('n.deleted = 0');

        if ($internalName) {
            $qb
                ->andWhere('n.internalName = :internalName')
                ->setParameter('internalName', $internalName)
            ;
        }
        if (null !== $hiddenFromNav && 'false' !== $hiddenFromNav) {
            $qb
                ->andWhere('n.hiddenFromNav = :hiddenFromNav')
                ->setParameter('hiddenFromNav', $hiddenFromNav === 'true' ? 1 : 0)
            ;
        }
        if ($refEntityName) {
            $qb
                ->andWhere('n.refEntityName = :refEntityName')
                ->setParameter('refEntityName', $refEntityName)
            ;
        }
        if ($locale) {
            $qb
                ->innerJoin('n.nodeTranslations', 't', 'WITH', 't.lang = :lang')
                ->setParameter('lang', $locale)
            ;
        }

        $context = new Context();

        if ($includeChildren) {
            $context->addGroup('with_children');
        }

        $view = new View(
            $this->getPaginator()->getPaginatedQueryBuilderResult($qb, $page, $limit)
        );
        $view->setContext($context);

        return $view;
    }

    /**
     * Retrieve a single node
     *
     * @OA\Get(
     *     path="/api/nodes/{id}",
     *     description="Get a node by ID",
     *     operationId="getNode",
     *     tags={"nodes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The node ID",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/Node")
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
     * @Rest\Get("/nodes/{id}")
     * @Rest\View(statusCode=200, serializerGroups={"Default"})
     *
     * @param int $id
     * @return Node
     */
    public function getNodeAction($id)
    {
        return $this->em->getRepository('KunstmaanNodeBundle:Node')->find($id);
    }

    /**
     * Retrieve a single node's children
     *
     * @OA\Get(
     *     path="/api/nodes/{id}/children",
     *     description="Retrieve a single node's children",
     *     operationId="getNodeChildren",
     *     tags={"nodes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The node ID",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="includeChildren",
     *         in="query",
     *         description="Do you want to include node children?",
     *         required=false,
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
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/PaginatedNodeList")
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
     * @QueryParam(name="includeChildren", nullable=true, allowBlank=true, default=false, requirements="(true|false)", description="If true, child nodes will be included recursively", strict=true)
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page", strict=true)
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results", strict=true)
     *
     * @Rest\Get("/nodes/{id}/children")
     * @Rest\View(statusCode=200, serializerGroups={"Default"})
     *
     * @param ParamFetcher $paramFetcher
     * @param int $id
     *
     * @return View
     */
    public function getNodeChildrenAction(ParamFetcher $paramFetcher, $id)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $includeChildren = $paramFetcher->get('includeChildren');


        /** @var Node $node */
        $node = $this->em->getRepository('KunstmaanNodeBundle:Node')->find($id);
        $data = $node->getChildren();


        $context = new Context();
        $context->addGroup('get');

        if ($includeChildren) {
            $context->addGroup('with_children');
        }

        $view = new View(
            $this->getPaginator()->getPaginatedArrayResult($data->toArray(), $page, $limit)
        );
        $view->setContext($context);

        return $view;
    }

    /**
     * Retrieve a single node's parent
     *
     * @OA\Get(
     *     path="/api/nodes/{id}/parent",
     *     description="Retrieve a single node's parent",
     *     operationId="getNodeParent",
     *     tags={"nodes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The node ID",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/Node")
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
     * @Rest\Get("/nodes/{id}/parent")
     * @Rest\View(statusCode=200, serializerGroups={"Default"})
     */
    public function getNodeParentAction($id)
    {
        $node = $this->em->getRepository('KunstmaanNodeBundle:Node')->find($id);

        return $node->getParent();
    }

    /**
     * Get entity instance
     *
     * @param integer $id
     *
     * @return Node
     */
    protected function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('KunstmaanNodeBundle:Node')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find node entity');
        }

        return $entity;
    }
}
