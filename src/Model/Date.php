<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;

// @todo switch to CarbonImmutable?
class Date implements ValueObject, \JsonSerializable
{
    public const STRING_FORMAT = 'Y-m-d';
    public const TZ = 'UTC';

    /** @var Carbon */
    private $date;

    public static function fromString(string $string): self
    {
        // timezone is only used if it's not in the date string
        return new static(new Carbon($string, self::TZ));
    }

    public static function now(string $tz = self::TZ): self
    {
        return new static(new Carbon('now', $tz));
    }

    public static function fromDateTime(\DateTimeInterface $date): self
    {
        return new static(Carbon::instance($date));
    }

    private function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    public function toString(): string
    {
        return $this->format(self::STRING_FORMAT);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->date->jsonSerialize();
    }

    /**
     * Compares up to milliseconds. Ignores microseconds.
     *
     * @param Date|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return 0 === $this->date->diffInMilliseconds($other->date);
    }
}
