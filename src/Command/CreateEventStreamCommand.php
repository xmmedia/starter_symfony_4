<?php

declare(strict_types=1);

namespace App\Command;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateEventStreamCommand extends Command
{
    /** @var EventStore */
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        parent::__construct();

        $this->eventStore = $eventStore;
    }

    protected function configure()
    {
        $this->setName('event-store:event-stream:create')
            ->setDescription('Create event_stream.')
            ->setHelp('This command creates the event_stream')
            ->addArgument(
                'stream_name',
                InputArgument::REQUIRED,
                'The name of the event stream. This will also be used in the table name. Don\'t include "_stream".'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventStore->create(
            new Stream(
                new StreamName($input->getArgument('stream_name')),
                new \ArrayIterator([])
            )
        );

        $output->writeln('<info>Event stream was created successfully.</info>');
    }
}
