<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Infrastructure\GraphQl\Mutation\User\AdminUserVerifyMutation;
use App\Model\User\Command\VerifyUserByAdmin;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserVerifyMutationTest extends BaseTestCase
{
    public function testActivate(): void
    {
        $faker = $this->faker();

        $data = [
            'userId' => $faker->uuid,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(VerifyUserByAdmin::class))
            ->andReturn(new Envelope(new \stdClass()));

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserVerifyMutation(
            $commandBus
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }
}