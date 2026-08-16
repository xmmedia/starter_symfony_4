<?php

declare(strict_types=1);

namespace App\Model\User;

use Xm\SymfonyBundle\Model\PhoneNumber;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Util\StringUtil;

final readonly class UserData implements ValueObject
{
    public static function fromArray(array $data): self
    {
        $phoneNumber = StringUtil::trim($data['phoneNumber'] ?? null);
        if (null !== $phoneNumber) {
            if (\is_array($data['phoneNumber'])) {
                $phoneNumber = PhoneNumber::fromArray($data['phoneNumber']);
            } else {
                $phoneNumber = PhoneNumber::fromString($data['phoneNumber']);
            }
        }

        return new self($phoneNumber);
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

    public function sameValueAs(ValueObject $other): bool
    {
        if (self::class !== $other::class) {
            return false;
        }

        return $this->toArray() === $other->toArray();
    }
}
