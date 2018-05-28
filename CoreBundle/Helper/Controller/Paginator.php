<?php

namespace Kunstmaan\Rest\CoreBundle\Helper\Controller;

use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class Paginator
{
    public function f(AdapterInterface $adapter, \Closure $decorator = null) {
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        $items = $pagerfanta->getCurrentPageResults();
        if (null !== $decorator) {
            array_walk($items, $decorator);
        }

        return new PaginatedRepresentation(
            new CollectionRepresentation($items),
            'get_nodes',
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