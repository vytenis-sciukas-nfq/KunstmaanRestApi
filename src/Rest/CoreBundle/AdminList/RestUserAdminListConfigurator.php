<?php

namespace Kunstmaan\Rest\CoreBundle\AdminList;

use Kunstmaan\UserManagementBundle\AdminList\UserAdminListConfigurator as ParentAdminListConfigurator;

/**
 * User admin list configurator
 */
class RestUserAdminListConfigurator extends ParentAdminListConfigurator
{
    private $editTemplate = 'KunstmaanRestCoreBundle:Default:add_or_edit.html.twig';

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'RestUser';
    }

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'KunstmaanRestCoreBundle';
    }
}