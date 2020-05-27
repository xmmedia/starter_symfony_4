<?php

declare(strict_types=1);

namespace App\Model\Page\Command;

use App\Model\Page\PageId;
use App\Model\Page\Template;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;

final class ChangePageTemplate extends Command
{
    public static function to(PageId $pageId, Template $template): self
    {
        return new self([
            'pageId'   => $pageId->toString(),
            'template' => $template->toString(),
        ]);
    }

    public function pageId(): PageId
    {
        return PageId::fromString($this->payload()['pageId']);
    }

    public function template(): Template
    {
        return Template::fromString($this->payload()['template']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'pageId');
        Assert::uuid($payload['pageId']);

        Assert::keyExists($payload, 'template');
        Assert::string($payload['template']);

        parent::setPayload($payload);
    }
}
