<?php

declare(strict_types=1);

namespace App\Model\Page\Command;

use App\Model\Page\PageId;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class DeletePage extends Command
{
    public static function now(PageId $pageId): self
    {
        return new self([
            'pageId' => $pageId->toString(),
        ]);
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->payload()['pageId']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'pageId');
        Assert::uuid($payload['pageId']);

        parent::setPayload($payload);
    }
}