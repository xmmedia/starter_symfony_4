<?php

declare(strict_types=1);

namespace App\Model\User;

use Xm\SymfonyBundle\Model\PhoneNumber;
use Xm\SymfonyBundle\Model\ValueObject;

final readonly class UserData implements ValueObject
{
    public static function fromArray(array $data): self
    {
        if (null !== $data['phoneNumber'] ?? null) {
            if (\is_array($data['phoneNumber'])) {
                $data['phoneNumber'] = PhoneNumber::fromArray($data['phoneNumber']);
            } else {
                $data['phoneNumber'] = PhoneNumber::fromString($data['phoneNumber']);
            }
        }

        return new self($data['phoneNumber'] ?? null);
    }

    private function __construct(private ?PhoneNumber $phoneNumber)
    {
    }

    public function phoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function toArray(): array
    {
        return [
            'phoneNumber' => $this->phoneNumber()?->toArray(),
        ];
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (self::class !== $other::class) {
            return false;
        }

        return $this->toArray() === $other->toArray();
    }
}
