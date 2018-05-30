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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Repository\NodeRepository;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Kunstmaan\Rest\NodeBundle\Form\RestNodeType;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NodesController
 */
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
     * @SWG\Get(
     *     path="/api/nodes",
     *     description="Get all nodes",
     *     operationId="getNodes",
     *     produces={"application/json"},
     *     tags={"nodes"},
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
     *         name="internalName",
     *         in="query",
     *         type="string",
     *         description="The internal name of the node",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="locale",
     *         in="query",
     *         type="string",
     *         description="Locale",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="hiddenFromNav",
     *         in="query",
     *         type="boolean",
     *         description="If 1, only nodes hidden from nav will be returned",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="refEntityName",
     *         in="query",
     *         type="string",
     *         description="Which pages you want to have returned",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/NodeList")
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     *
     * @QueryParam(name="internalName", nullable=true, description="The internal name of the node", requirements="[\w\d_-]+", strict=true)
     * @QueryParam(name="hiddenFromNav", nullable=true, default=null, description="If true, only nodes hidden from nav will be returned")
     * @QueryParam(name="refEntityName", nullable=true, default=null, description="Which pages you want to have returned")
     * @QueryParam(name="locale", nullable=true, default=null, requirements="[a-zA-Z_-]+", strict=true, description="If you provide a locale, then only nodes with a node translation of this locale will be returned")
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     *
     * @param ParamFetcher $paramFetcher
     *
     * @return Response
     */
    public function getNodesAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $internalName = $paramFetcher->get('internalName');
        $hiddenFromNav = $paramFetcher->get('hiddenFromNav');
        $refEntityName = $paramFetcher->get('refEntityName');
        $locale = $paramFetcher->get('locale');

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
        if (null !== $hiddenFromNav && '' !== $hiddenFromNav) {
            $qb
                ->andWhere('n.hiddenFromNav = :hiddenFromNav')
                ->setParameter('hiddenFromNav', $hiddenFromNav == 'true' ? 1 : 0)
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

        $paginator = $this->getPaginator();
        $paginatedCollection = $paginator->getPaginatedQueryBuilderResult($qb, $page, $limit);

        return $this->handleView($this->view($paginatedCollection, Response::HTTP_OK));
    }

    /**
     * Retrieve a single node
     *
     * @View(
     *     statusCode=200
     * )
     *
     * @SWG\Get(
     *     path="/api/nodes/{id}",
     *     description="Get a node by ID",
     *     operationId="getNode",
     *     produces={"application/json"},
     *     tags={"nodes"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The node ID",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/Node")
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     */
    public function getNodeAction($id)
    {
        $data = $this->em->getRepository('KunstmaanNodeBundle:Node')->find($id);

        return $this->handleView($this->view($data, Response::HTTP_OK));
    }

    /**
     * Retrieve a single node's children
     *
     * @View(
     *     statusCode=200
     * )
     *
     * @SWG\Get(
     *     path="/api/nodes/{id}/children",
     *     description="Retrieve a single node's children",
     *     operationId="getNodeChildren",
     *     produces={"application/json"},
     *     tags={"nodes"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The node ID",
     *         required=true,
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
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/NodeList")
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
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
     * @param ParamFetcher $paramFetcher
     * @param int $id
     */
    public function getNodeChildrenAction(ParamFetcher $paramFetcher, $id)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');

        /** @var Node $node */
        $node = $this->em->getRepository('KunstmaanNodeBundle:Node')->find($id);
        $data = $node->getChildren();

        $paginator = $this->getPaginator();
        $paginatedCollection = $paginator->getPaginatedArrayResult($data->toArray(), $page, $limit);

        return $this->handleView($this->view($paginatedCollection, Response::HTTP_OK));
    }

    /**
     * Retrieve a single node's parent
     *
     * @SWG\Get(
     *     path="/api/nodes/{id}/parent",
     *     description="Retrieve a single node's parent",
     *     operationId="getNodeParent",
     *     produces={"application/json"},
     *     tags={"nodes"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         type="integer",
     *         description="The node ID",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/Node")
     *     ),
     *     @SWG\Response(
     *         response=403,
     *         description="Returned when the user is not authorized to fetch nodes",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     )
     * )
     */
    public function getNodeParentAction($id)
    {
        $node = $this->em->getRepository('KunstmaanNodeBundle:Node')->find($id);
        $data = $node->getParent();

        return $this->handleView($this->view($data, Response::HTTP_OK));
    }

    /**
     * Get entity instance
     *
     * @param integer $id
     *
     * @return Organisation
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
