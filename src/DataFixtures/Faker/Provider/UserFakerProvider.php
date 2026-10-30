<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Model\User\Role;
use App\Model\User\UserData;
use Faker;

/**
 * @codeCoverageIgnore
 */
class UserFakerProvider extends Faker\Provider\Base
{
    /** @var Faker\Generator&\App\DataFixtures\Faker\FakerGenerator */
    #[\Override]
    protected $generator;

    public function userRole(): Role
    {
        return $this->generator->randomElement(
            array_filter(Role::cases(), static fn (Role $role): bool => Role::ROLE_ALLOWED_TO_SWITCH !== $role),
        );
    }

    public function userData(): UserData
    {
        return UserData::fromArray([
            'phoneNumber' => $this->generator->boolean() ? $this->generator->phoneNumberVo()->national() : null,
        ]);
    }
}
