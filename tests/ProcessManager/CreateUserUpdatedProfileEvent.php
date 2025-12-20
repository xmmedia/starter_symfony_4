<?php

declare(strict_types=1);

namespace App\Tests\ProcessManager;

use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Name;
use App\Model\User\UserId;

trait CreateUserUpdatedProfileEvent
{
    private function createUserUpdatedProfileEvent(UserId $userId): UserUpdatedProfile
    {
        $faker = $this->faker();

        return UserUpdatedProfile::now(
            $userId,
            $faker->emailVo(),
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            $faker->userData(),
        );
    }
}
