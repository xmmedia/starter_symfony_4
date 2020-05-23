<?php

declare(strict_types=1);

namespace App\Model\Page;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

class Title implements ValueObject
{
    public const MAX_LENGTH = 191;

    /** @var string */
    private $title;

    public static function fromString(string $title): self
    {
        return new self($title);
    }

    private function __construct(string $title)
    {
        $title = StringUtil::trim($title);

        Assert::notEmpty($title, 'The page title cannot be empty.');
        Assert::maxLength($title, self::MAX_LENGTH, 'The page title cannot be longer than 191 characters.');

        $this->title = $title;
    }

    public function toString(): string
    {
        return $this->title;
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

        return $this->title === $other->title;
    }
}
