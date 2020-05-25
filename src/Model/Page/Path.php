<?php

declare(strict_types=1);

namespace App\Model\Page;

use App\Util\Slugger;
use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

class Path implements ValueObject
{
    public const MAX_LENGTH = 191;

    /** @var string */
    private $path;

    /**
     * Runs the path through a slugger to remove non-ascii characters.
     */
    public static function fromUserString(string $path): self
    {
        return new self($path, true);
    }

    public static function fromString(string $path): self
    {
        return new self($path);
    }

    private function __construct(string $path, bool $slugify = false)
    {
        $trimmedPath = StringUtil::trim($path);

        Assert::notEmpty($trimmedPath, 'The path cannot be empty.');
        Assert::startsWith($trimmedPath, '/', 'The path must start with a slash (%2$s).');
        Assert::maxLength($trimmedPath, self::MAX_LENGTH, 'The path cannot be longer than 191 characters.');

        if ($slugify) {
            $this->path = Slugger::path($trimmedPath);
        } else {
            // keep the original so we don't change the path to a page
            $this->path = $path;
        }
    }

    public function toString(): string
    {
        return $this->path;
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
