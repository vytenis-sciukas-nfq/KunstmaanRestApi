<?php

namespace Kunstmaan\Rest\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\BaseUser;

/**
 * User entity
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\AdminBundle\Repository\UserRepository")
 * @ORM\Table(name="kuma_rest_users")
 */
class RestUser extends BaseUser implements HasApiKeyInterface
{
    /**
     * @ORM\Column(type="string", name="api_key", nullable=true)
     */
    protected $apiKey;

    /**
     * Get the classname of the formtype.
     *
     * @return string
     */
    public function getFormTypeClass()
    {
        return 'Kunstmaan\Rest\CoreBundle\Form\RestUserType';
    }

    /**
     * Get the classname of the admin list configurator.
     *
     * @return string
     */
    public function getAdminListConfiguratorClass()
    {
        return 'Kunstmaan\Rest\CoreBundle\AdminList\RestUserAdminListConfigurator';
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }
}