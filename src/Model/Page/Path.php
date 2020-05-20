<?php

declare(strict_types=1);

namespace App\Model\Page;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

class Path implements ValueObject
{
    public const MAX_LENGTH = 191;

    /** @var string */
    private $path;

    public static function fromString(string $path): self
    {
        return new self($path);
    }

    private function __construct(string $path)
    {
        $path = StringUtil::trim($path);

        Assert::notEmpty($path, 'The path cannot be empty');
        Assert::startsWith($path, '/', 'The path must start with a slash (%2$s)');
        Assert::maxLength($path, self::MAX_LENGTH, 'The path cannot be longer than 191 characters');

        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function toString(): string
    {
        return $this->path();
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

        return $this->path === $other->path;
    }
}
