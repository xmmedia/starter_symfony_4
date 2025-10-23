<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Tests\BaseTestCase;

class ActivateUserByAdminTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = ActivateUserByAdmin::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
