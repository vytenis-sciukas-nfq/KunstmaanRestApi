<?php

namespace Kunstmaan\Rest\UserBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\ControllerTrait;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Helper\File\FileHelper;
use Kunstmaan\MediaBundle\Helper\MediaManager;
use Kunstmaan\MediaBundle\Repository\FolderRepository;
use Kunstmaan\MediaBundle\Repository\MediaRepository;
use Kunstmaan\Rest\CoreBundle\Controller\AbstractApiController;
use Kunstmaan\Rest\CoreBundle\Entity\RestUser;
use Kunstmaan\Rest\MediaBundle\Model\MediaModel;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
     *         name="userName",
     *         in="query",
     *         type="string",
     *         description="The username of user",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         type="string",
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
     * @QueryParam(name="userName", nullable=true, description="The username of user", requirements="[\w\d_-]+", strict=true)
     * @QueryParam(name="page", nullable=false, default="1", requirements="\d+", description="The current page")
     * @QueryParam(name="limit", nullable=false, default="20", requirements="\d+", description="Amount of results")
     * @QueryParam(name="email", nullable=true, description="the email of user", requirements="[\w\d_-]+", strict=true)
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
        $userName = $paramFetcher->get('userName');
        $email = $paramFetcher->get('email');

        /** @var MediaRepository $repository */
        $repository = $this->doctrine->getRepository(RestUser::class);
        $qb = $repository->createQueryBuilder('n');

        if ($userName) {
            $qb
                ->andWhere('n.username LIKE :username')
                ->setParameter('username', '%'.addcslashes($userName, '%_').'%');
        }
        if ($email) {
            $qb
                ->andWhere('n.email LIKE :email')
                ->setParameter('email', '%'.addcslashes($email, '%_').'%');
        }

        return $this->getPaginator()->getPaginatedQueryBuilderResult($qb, $page, $limit);
    }
}
