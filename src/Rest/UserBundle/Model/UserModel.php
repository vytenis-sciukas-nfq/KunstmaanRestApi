<?php

namespace Kunstmaan\Rest\UserBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserModel
 */
class UserModel
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"list", "update", "create"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $username;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"list", "update", "create"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $email;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"list", "update", "create"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $adminLocale;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"list", "update", "create"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $password;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("passwordConfirm")
     * @JMS\Groups({"list", "update", "create"})
     * @Assert\NotBlank(groups={"create"})
     */
    private $passwordConfirm;

    /**
     * @var boolean
     * @JMS\Type("boolean")
     * @JMS\Groups({"list", "update"})
     */
    private $enabled;

    /**
     * @var array
     * @JMS\Type("array<Kunstmaan\AdminBundle\Entity\Group>")
     * @Assert\NotBlank(groups={"create"})
     * @JMS\Groups({"list", "update", "create"})
     */
    private $groups = [];

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminLocale()
    {
        return $this->adminLocale;
    }

    /**
     * @param string $adminLocale
     * @return $this
     */
    public function setAdminLocale(string $adminLocale)
    {
        $this->adminLocale = $adminLocale;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     * @return $this
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordConfirm()
    {
        return $this->passwordConfirm;
    }

    /**
     * @param string $passwordConfirm
     * @return $this
     */
    public function setPasswordConfirm(string $passwordConfirm)
    {
        $this->passwordConfirm = $passwordConfirm;

        return $this;
    }


}
