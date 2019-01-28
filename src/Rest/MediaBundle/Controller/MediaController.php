<?php

/*
 * This file is part of the KunstmaanBundlesCMS package.
 *
 * (c) Kunstmaan <https://github.com/Kunstmaan/KunstmaanBundlesCMS/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kunstmaan\Rest\MediaBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Repository\MediaRepository;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Swagger\Annotations as SWG;


/**
 * Class MediaController
 *
 */
class MediaController extends AbstractApiController
{
    use ControllerTrait;

    /**
     * Retrieve media paginated
     *
     * @SWG\Get(
     *     path="/api/media",
     *     description="Get all media",
     *     operationId="getMedia",
     *     produces={"application/json"},
     *     tags={"media"},
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
     *         name="name",
     *         in="query",
     *         type="string",
     *         description="The name of the media",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="folderId",
     *         in="query",
     *         type="integer",
     *         description="The id of the folder to limit the search to",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/MediaList")
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
     * @QueryParam(name="name", nullable=true, description="The internal name of the node", requirements="[\w\d_-]+", strict=true)
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     * @QueryParam(name="folderId", nullable=true, requirements="\d+", description="folder id", strict=true)
     *
     * @Rest\Get("/media")
     * @View(statusCode=200)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PaginatedRepresentation
     */
    public function getAllMediaAction(ParamFetcherInterface $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('limit');
        $name = $paramFetcher->get('name');
        $folderId = $paramFetcher->get('folderId');

        /** @var MediaRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Media::class);
        $qb = $repository->createQueryBuilder('n');
        $qb->where('n.deleted = 0');

        if ($folderId) {
            $qb
                ->andWhere('n.folder = :folder')
                ->setParameter('folder', $folderId)
            ;
        }
        if ($name) {
            $qb
                ->andWhere('n.name LIKE :name')
                ->setParameter('name', '%' . addcslashes($name, '%_'). '%')
            ;
        }

        return $this->getPaginator()->getPaginatedQueryBuilderResult($qb, $page, $limit);
    }
}
