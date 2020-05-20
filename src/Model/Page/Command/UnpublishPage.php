<?php

declare(strict_types=1);

namespace App\Model\Page\Command;

use App\Model\Page\PageId;
use App\Model\Page\Path;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class UnpublishPage extends Command
{
    public static function to(PageId $pageId): self
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
