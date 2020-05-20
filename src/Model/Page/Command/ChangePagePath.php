<?php

declare(strict_types=1);

namespace App\Model\Page\Command;

use App\Model\Page\PageId;
use App\Model\Page\Path;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class ChangePagePath extends Command
{
    public static function to(PageId $pageId, Path $path): self
    {
        return new self([
            'pageId' => $pageId->toString(),
            'path'   => $path->toString(),
        ]);
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->payload()['pageId']);
    }

    public function path(): Path
    {
        return Path::fromString($this->payload()['path']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'pageId');
        Assert::uuid($payload['pageId']);

        Assert::keyExists($payload, 'path');
        Assert::string($payload['path']);

        parent::setPayload($payload);
    }
}
