<?php

/*
 * This file is part of the KunstmaanBundlesCMS package.
 *
 * (c) Kunstmaan <https://github.com/Kunstmaan/KunstmaanBundlesCMS/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kunstmaan\Rest\NodeBundle\Model;

use Kunstmaan\ArticleBundle\Entity\AbstractAuthor;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\SeoBundle\Entity\Seo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ApiPage
 */
class ApiPage
{
    /**
     * @var ApiEntity
     * @Assert\Valid()
     */
    private $page;

    /**
     * @var Node
     * @Assert\Valid()
     */
    private $node;

    /**
     * @var Seo
     * @Assert\Valid()
     */
    private $seo;

    /**
     * @var AbstractAuthor
     * @Assert\Valid()
     */
    private $author;

    /**
     * @var NodeTranslation
     * @Assert\Valid()
     */
    private $nodeTranslation;

    /**
     * @var NodeVersion
     * @Assert\Valid()
     */
    private $nodeVersion;

    /**
     * @var ApiPageTemplate
     * @Assert\Valid()
     */
    private $pageTemplate;

    /**
     * @return ApiEntity
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param ApiEntity $page
     */
    public function setPage(ApiEntity $page)
    {
        $this->page = $page;
    }

    /**
     * @return ApiPageTemplate
     */
    public function getPageTemplate()
    {
        return $this->pageTemplate;
    }

    /**
     * @param ApiPageTemplate $pageTemplate
     */
    public function setPageTemplate(ApiPageTemplate $pageTemplate)
    {
        $this->pageTemplate = $pageTemplate;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param Node $node
     *
     * @return $this
     */
    public function setNode(Node $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * @return NodeTranslation
     */
    public function getNodeTranslation()
    {
        return $this->nodeTranslation;
    }

    /**
     * @param NodeTranslation $nodeTranslation
     *
     * @return $this
     */
    public function setNodeTranslation(NodeTranslation $nodeTranslation)
    {
        $this->nodeTranslation = $nodeTranslation;

        return $this;
    }

    /**
     * @return NodeVersion
     */
    public function getNodeVersion()
    {
        return $this->nodeVersion;
    }

    /**
     * @param NodeVersion $nodeVersion
     *
     * @return $this
     */
    public function setNodeVersion(NodeVersion $nodeVersion)
    {
        $this->nodeVersion = $nodeVersion;

        return $this;
    }

    /**
     * @return AbstractAuthor
     */
    public function getAuthor(): AbstractAuthor
    {
        return $this->author;
    }

    /**
     * @param AbstractAuthor $author
     * @return $this
     */
    public function setAuthor(AbstractAuthor $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Seo
     */
    public function getSeo()
    {
        return $this->seo;
    }

    /**
     * @param Seo $seo
     * @return $this
     */
    public function setSeo($seo)
    {
        $this->seo = $seo;

        return $this;
    }
}
