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

    public function getAddUrlFor(array $params = array())
    {
        $params = array_merge($params, $this->getExtraParameters());

        $friendlyName = explode('\\', $this->getEntityName());
        $friendlyName = array_pop($friendlyName);
        $re = '/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/';
        $a = preg_split($re, $friendlyName);
        $superFriendlyName = 'User';

        return array(
            $superFriendlyName => array(
                'path' => $this->getPathByConvention($this::SUFFIX_ADD),
                'params' => $params,
            ),
        );
    }
}
