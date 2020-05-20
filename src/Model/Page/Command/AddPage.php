<?php

declare(strict_types=1);

namespace App\Model\Page\Command;

use App\Model\Page\PageId;
use App\Model\Page\ParentId;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class AddPage extends Command
{
    public static function to(
        PageId $page,
        ParentId $parentId
    ): self {
        return new self([
            'pageId'     => $page->toString(),
            'parentId'   => $parentId->toString(),
        ]);
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->payload()['pageId']);
    }

    public function parentId(): ParentId
    {
        return ParentId::fromString($this->payload()['parentId']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'pageId');
        Assert::uuid($payload['pageId']);

        Assert::keyExists($payload, 'parentId');
        Assert::uuid($payload['parentId']);

        parent::setPayload($payload);
    }
}
