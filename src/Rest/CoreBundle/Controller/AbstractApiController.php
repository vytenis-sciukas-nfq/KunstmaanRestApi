<?php

/*
 * This file is part of the KunstmaanBundlesCMS package.
 *
 * (c) Kunstmaan <https://github.com/Kunstmaan/KunstmaanBundlesCMS/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kunstmaan\Rest\CoreBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * Class AbstractApiController
 */
abstract class AbstractApiController extends AbstractFOSRestController
{
    /**
     * @return \Kunstmaan\Rest\CoreBundle\Helper\Controller\Paginator|object
     */
    public function getPaginator()
    {
        return $this->container->get('kunstmaan_rest_core.helper.controller.paginator');
    }
}
