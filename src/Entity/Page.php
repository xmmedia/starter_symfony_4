<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\Page\PageId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Projection\Page\PageFinder")
 */
class Page
{
    /**
     * @var \Ramsey\Uuid\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private $pageId;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    private $path;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    private $template;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $published;

    /**
     * @var string
     * @ORM\Column(type="string", length=191)
     */
    private $title;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private $content;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $lastModified;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="user_id", nullable=true)
     */
    private $lastModifiedBy;

    public function pageId(): PageId
    {
        return PageId::fromUuid($this->pageId);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function template(): string
    {
        return $this->template;
    }

    public function published(): bool
    {
        return $this->published;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): array
    {
        return $this->content;
    }

    public function lastModified(): \DateTimeImmutable
    {
        return $this->lastModified;
    }

    public function lastModifiedBy(): ?User
    {
        return $this->lastModifiedBy;
    }
}
