<?php

namespace Kunstmaan\Rest\CoreBundle\Helper\Controller;

use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class Paginator
{
    /**
     * @param \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder $query
     * @param int $page
     * @param int $limit
     * @param \Closure|null $closure
     * @param string $route
     * @return PaginatedRepresentation
     */
    public function getPaginatedQueryBuilderResult($query, $page = 0, $limit = 20, \Closure $closure = null, $route = 'get_nodes')
    {
        $adapter = new DoctrineORMAdapter($query);

        return $this->getPaginatedRepresentation($adapter, $page, $limit, $closure, $route);
    }

    /**
     * @param array $array
     * @param int $page
     * @param \Closure|null $closure
     * @param int $limit
     * @param string $route
     * @return PaginatedRepresentation
     */
    public function getPaginatedArrayResult(array $array, $page = 0, $limit = 20, \Closure $closure = null, $route = 'get_nodes')
    {
        $adapter = new ArrayAdapter($array);

        return $this->getPaginatedRepresentation($adapter, $page, $limit, $closure, $route);
    }

    /**
     * @param AdapterInterface $adapter
     * @param int $page
     * @param \Closure|null $decorator
     * @param int $limit
     * @return PaginatedRepresentation
     */
    protected function getPaginatedRepresentation(AdapterInterface $adapter, $page = 0, $limit = 20, \Closure $closure = null, $route = 'get_nodes') {
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        $items = $pagerfanta->getCurrentPageResults();
        if (null !== $closure) {
            $items = array_map($closure, (array) $items);
        }

        return new PaginatedRepresentation(
            new CollectionRepresentation($items),
            $route,
            [],
            $page,
            $limit,
            $pagerfanta->getNbPages(),
            null,
            null,
            false,
            $pagerfanta->getNbResults()
        );
    }
}