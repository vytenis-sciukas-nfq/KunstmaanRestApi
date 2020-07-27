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
use FOS\RestBundle\View\ViewHandlerInterface;
use Kunstmaan\Rest\CoreBundle\Helper\Controller\Paginator;

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

    public static function getSubscribedServices()
    {
        $subscribedServices = parent::getSubscribedServices();
        $subscribedServices['kunstmaan_rest_core.helper.controller.paginator'] = Paginator::class;

        return $subscribedServices;
    }
}
