<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CreateEventStreamCommand;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Symfony\Component\Console\Tester\CommandTester;

class CreateEventStreamCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $streamName = 'user';

        $eventStore = Mockery::mock(EventStore::class);
        $eventStore->shouldReceive('create')

            ->withArgs(function ($stream) use ($streamName) {
                /* @var Stream $stream */
                $this->assertInstanceOf(Stream::class, $stream);
                $this->assertEquals($streamName, $stream->streamName()->toString());

                return true;
            });

        $command = new CreateEventStreamCommand($eventStore);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['stream_name' => $streamName]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertContains('Event stream was created successfully.', $output);
    }
}
