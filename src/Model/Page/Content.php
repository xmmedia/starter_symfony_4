<?php

declare(strict_types=1);

namespace App\Model\Page;

use App\Util\Assert;
use Xm\SymfonyBundle\Model\ValueObject;

class Content implements ValueObject
{
    /** @var array */
    private $content;

    public static function fromArray(array $content): self
    {
        return new self($content);
    }

    public static function createDefaultContent(): self
    {
        return self::fromArray([
            'template'         => null,
            'visibleInSitemap' => true,
            'metaDescription'  => null,
        ]);
    }

    private function __construct(array $content)
    {
        Assert::keyExists($content, 'template', 'Content must have "template" key.');
        Assert::nullOrString($content['template'], '"template" must be a string or null.');

        Assert::keyExists($content, 'visibleInSitemap', 'Content must have "visibleInSitemap" key.');
        Assert::boolean($content['visibleInSitemap'], '"visibleInSitemap" must be a boolean.');

        Assert::keyExists($content, 'metaDescription', 'Content must have "metaDescription" key.');
        Assert::nullOrString($content['metaDescription'], '"metaDescription" must be a string or null.');

        $this->content = $content;
    }

    public function template(): ?string
    {
        return $this->content['template'];
    }

    public function toArray(): array
    {
        return $this->content;
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->content === $other->content;
    }
}
