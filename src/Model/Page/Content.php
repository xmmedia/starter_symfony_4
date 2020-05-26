<?php

declare(strict_types=1);

namespace App\Model\Page;

use App\Util\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

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
            'visibleInSitemap' => true,
            'metaDescription'  => null,
        ]);
    }

    private function __construct(array $content)
    {
        Assert::keyExists($content, 'visibleInSitemap', 'Content must have "visibleInSitemap" key.');
        Assert::boolean($content['visibleInSitemap'], '"visibleInSitemap" must be a boolean.');

        Assert::keyExists($content, 'metaDescription', 'Content must have "metaDescription" key.');
        Assert::nullOrString($content['metaDescription'], '"metaDescription" must be a string or null.');

        foreach ($content as $item => $value) {
            if (\in_array($item, ['visibleInSitemap', 'metaDescription'])) {
                continue;
            }

            Assert::isArray($value, 'All content values in content must be arrays. "'.$item.'" is a %1$s.');
            Assert::keyExists($value, 'type', 'The type key must exist. Missing on "'.$item.'".');
            Assert::keyExists($value, 'value', 'The value key must exist. Missing on "'.$item.'".');
        }

        $this->content = self::trimStringValues($content);
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

    public static function trimStringValues(array $content): array
    {
        foreach ($content as $key => $value) {
            if (\is_array($value) && \array_key_exists('value', $value) && \is_string($value['value'])) {
                $content[$key]['value'] = StringUtil::trim($value['value']);
            }
        }

        return $content;
    }
}
