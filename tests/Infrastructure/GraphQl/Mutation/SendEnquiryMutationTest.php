<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation;

use App\Infrastructure\GraphQl\Mutation\SendEnquiryMutation;
use App\Model\Enquiry\Command\SubmitEnquiry;
use App\Tests\BaseTestCase;
use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SendEnquiryMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'name'    => $faker->name,
            'email'   => $faker->email,
            'message' => $faker->string(50),
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(SubmitEnquiry::class))
            ->andReturn(new Envelope(new \stdClass()));

        $args = new Argument(['enquiry' => $data]);

        $result = (new SendEnquiryMutation($commandBus))($args);

        $this->assertEquals(['success' => true], $result);
    }

    /**
     * @dataProvider invalidMessageProvider
     */
    public function testInvalidMessage(?string $message): void
    {
        $faker = $this->faker();
        $data = [
            'name'    => $faker->name,
            'email'   => $faker->email,
            'message' => $message,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $args = new Argument(['enquiry' => $data]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message must be between');

        $result = (new SendEnquiryMutation($commandBus))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function invalidMessageProvider(): \Generator
    {
        $faker = $this->faker();

        yield [null];
        yield [''];
        yield [' '];
        yield [$faker->string(9)];
        yield [$faker->string(10000 + 1)];
    }
}
