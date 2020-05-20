<?php

declare(strict_types=1);

namespace App\Document;

use App\Model\User\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\ODM\PHPCR\ImmutableReferrersCollection;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\SeoBundle\SitemapAwareInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Symfony\Component\Routing\Route;

/**
 * @PHPCR\Document(referenceable=true)
 */
class Page implements
    TranslatableInterface,
    PublishableInterface,
    RouteReferrersInterface,
    SitemapAwareInterface
{
    /**
     * @var string
     * @PHPCR\Id
     */
    private $id;

    /**
     * @var string
     * @PHPCR\Uuid
     */
    private $uuid;

    /**
     * @var string
     * @PHPCR\Nodename
     */
    private $path;

    /**
     * @var Generic|Page|object
     * @PHPCR\ParentDocument
     */
    private $parentDocument;

    /**
     * @var \Doctrine\ODM\PHPCR\ChildrenCollection
     * @PHPCR\Children
     */
    private $children;

    /**
     * The language this document currently is in.
     *
     * @var string
     * @PHPCR\Locale
     */
    private $locale;

    /**
     * @var ArrayCollection|ImmutableReferrersCollection|RouteObjectInterface[]|Route[]
     * @PHPCR\Referrers(
     *     referringDocument="Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route",
     *     referencedBy="content"
     * )
     */
    protected $routes;

    /**
     * @var bool
     * @PHPCR\Field(type="boolean", property="publishable")
     */
    protected $isPublishable = false;

    /**
     * @var bool
     * @PHPCR\Field(type="boolean", property="visible_for_sitemap")
     */
    private $isVisibleInSitemap;

    /**
     * @var bool
     * @PHPCR\Field(type="boolean", property="homepage")
     */
    private $isHomepage = false;

    /**
     * @var string|null
     * @PHPCR\Field(type="string", nullable=true)
     */
    private $template;

    /**
     * @var string
     * @PHPCR\Field(type="string", translated=true)
     */
    private $title;

    /**
     * @var string|null
     * @PHPCR\Field(type="string", translated=true, nullable=true)
     */
    private $metaDescription;

    /**
     * @var string|null
     * @PHPCR\Field(type="string", translated=true, nullable=true)
     */
    private $content;

    /**
     * @var \DateTime
     * @PHPCR\Field(type="date")
     */
    private $created;

    /**
     * @var UserId|string|null
     * @PHPCR\Field(type="string", nullable=true)
     */
    private $createdBy;

    /**
     * @var \DateTime|null
     * @PHPCR\Field(type="date", nullable=true)
     */
    private $lastModified;

    /**
     * @var UserId|string|null
     * @PHPCR\Field(type="string", nullable=true)
     */
    private $lastModifiedBy;

    public function __construct()
    {
        $this->routes = new ArrayCollection();
    }

    /**
     * @param Generic|Page|object $parentDocument
     */
    public static function create(
        $parentDocument,
        string $path,
        ?string $template,
        string $title
    ): self {
        $page = new self();
        $page->parentDocument = $parentDocument;
        $page->path = $path;
        $page->template = $template;
        $page->title = $title;
        $page->isPublishable = true;
        $page->isVisibleInSitemap = true;
        $page->created = new \DateTime();

        return $page;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function fullPath(): string
    {
        return substr($this->id, \strlen('/cms/content') + 1);
    }

    public function parentDocument(): object
    {
        return $this->parentDocument;
    }

    public function children(): \Doctrine\ODM\PHPCR\ChildrenCollection
    {
        return $this->children;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale();
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute($route): self
    {
        $this->routes->add($route);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRoute($route): self
    {
        $this->routes->removeElement($route);

        return $this;
    }

    public function isPublishable(): bool
    {
        return $this->isPublishable;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublishable($boolean): self
    {
        $this->isPublishable = $boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isVisibleInSitemap($sitemap): bool
    {
        return $this->isVisibleInSitemap;
    }

    public function setIsVisibleInSitemap(bool $isVisibleInSitemap): self
    {
        $this->isVisibleInSitemap = $isVisibleInSitemap;

        return $this;
    }

    public function isHomepage(): bool
    {
        return $this->isHomepage;
    }

    public function makeHomepage(): self
    {
        $this->isHomepage = true;

        return $this;
    }

    public function makeNotHomepage(): self
    {
        $this->isHomepage = false;

        return $this;
    }

    public function template(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function metaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function created(): \DateTime
    {
        return $this->created;
    }

    public function setCreatedBy(UserId $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function createdBy(): UserId
    {
        if (\is_string($this->createdBy)) {
            return UserId::fromString($this->createdBy);
        }

        return $this->createdBy;
    }

    public function lastModified(): ?\DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(\DateTime $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    public function lastModifiedBy(): ?UserId
    {
        if (null === $this->lastModifiedBy) {
            return null;
        }

        if (\is_string($this->lastModifiedBy)) {
            return UserId::fromString($this->lastModifiedBy);
        }

        return $this->lastModifiedBy;
    }

    public function setLastModifiedBy(UserId $lastModifiedBy): self
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }
}
