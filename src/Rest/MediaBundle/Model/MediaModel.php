<?php

namespace Kunstmaan\Rest\MediaBundle\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MediaModel
 */
class MediaModel
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     */
    private $folderId = 1;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups("list")
     */
    private $name;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups("list")
     */
    private $url;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups("list")
     */
    private $content;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups("list")
     */
    private $description;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups("list")
     */
    private $copyRight;

    /**
     * @return int
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * @param int $folderId
     * @return $this
     */
    public function setFolderId(int $folderId)
    {
        $this->folderId = $folderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getCopyRight()
    {
        return $this->copyRight;
    }

    /**
     * @param string $copyRight
     * @return $this
     */
    public function setCopyRight(string $copyRight)
    {
        $this->copyRight = $copyRight;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }
}
