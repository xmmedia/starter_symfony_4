<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Name;
use App\Tests\BaseTestCase;

class UpdateUserProfileTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $command = UpdateUserProfile::with($userId, $email, $firstName, $lastName);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertTrue($firstName->sameValueAs($command->firstName()));
        $this->assertTrue($lastName->sameValueAs($command->lastName()));
    }
}
