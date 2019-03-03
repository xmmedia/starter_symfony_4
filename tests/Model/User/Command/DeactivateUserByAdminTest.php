<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\DeactivateUserByAdmin;
use App\Tests\BaseTestCase;

class DeactivateUserByAdminTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $command = DeactivateUserByAdmin::user($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
