<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\SendProfileUpdatedNotification;
use App\Tests\BaseTestCase;

class SendProfileUpdatedNotificationTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = SendProfileUpdatedNotification::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
