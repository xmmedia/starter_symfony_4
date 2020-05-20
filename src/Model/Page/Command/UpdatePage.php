<?php

declare(strict_types=1);

namespace App\Model\Page\Command;

use App\Model\Page\Content;
use App\Model\Page\PageId;
use App\Model\Page\Title;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class UpdatePage extends Command
{
    public static function to(
        PageId $pageId,
        Title $title,
        Content $content
    ): self {
        return new self([
            'pageId'  => $pageId->toString(),
            'title'   => $title->toString(),
            'content' => $content->toArray(),
        ]);
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->payload()['pageId']);
    }

    public function title(): Title
    {
        return Title::fromString($this->payload()['title']);
    }

    public function content(): Content
    {
        return Content::fromArray($this->payload()['content']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'pageId');
        Assert::uuid($payload['pageId']);

        Assert::keyExists($payload, 'title');
        Assert::string($payload['title']);

        Assert::keyExists($payload, 'content');
        Assert::isArray($payload['content']);

        parent::setPayload($payload);
    }
}
