<?php

declare(strict_types=1);

namespace App\Model\Page;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

class Template implements ValueObject
{
    public const MAX_LENGTH = 191;

    /** @var string */
    private $template;

    public static function fromString(string $template): self
    {
        return new self($template);
    }

    private function __construct(string $template)
    {
        $template = StringUtil::trim($template);

        Assert::notEmpty($template, 'The template cannot be empty.');
        Assert::maxLength($template, self::MAX_LENGTH, 'The template cannot be longer than 191 characters.');

        $this->template = $template;
    }

    public function toString(): string
    {
        return $this->template;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->template === $other->template;
    }
}
