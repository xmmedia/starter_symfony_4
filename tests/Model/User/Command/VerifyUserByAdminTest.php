<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\VerifyUserByAdmin;
use App\Tests\BaseTestCase;

class VerifyUserByAdminTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = VerifyUserByAdmin::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
