<?php

declare(strict_types=1);

namespace App\Model\Page;

use Xm\SymfonyBundle\Model\ValueObject;

class Content implements ValueObject
{
    /** @var array */
    private $content;

    public static function fromArray(array $content): self
    {
        return new self($content);
    }

    private function __construct(array $content)
    {
        $this->content = $content;
    }

    public function content(): array
    {
        return $this->content;
    }

    public function toArray(): array
    {
        return $this->content();
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
