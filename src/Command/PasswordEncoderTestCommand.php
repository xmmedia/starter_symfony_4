<?php

declare(strict_types=1);

namespace App\Command;

use App\Security\TokenGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class PasswordEncoderTestCommand extends ContainerAwareCommand
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        parent::__construct();

        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setName('app:security:password-encode-test')
            ->setDescription('Basic script to test speed & memory consumption of password encoder algorithm.')
            ->addArgument(
                'count',
                InputArgument::REQUIRED,
                'The number of times to run the test.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Password Encode Test');

        $stopwatch = new Stopwatch();

        $times = $memories = [];

        for ($i = 0; $i <= $input->getArgument('count'); $i ++) {
            $stopwatch->start('encode-'.$i);

            $this->passwordEncoder->encodePassword(
                new \App\Entity\User(),
                (new TokenGenerator())()
            );

            $event = $stopwatch->stop('encode-'.$i);

            $times[] = $event->getDuration();
            $memories[] = $event->getMemory();
        }

        $io->writeln(
            sprintf('Average time: %d ms', array_sum($times)/count($times))
        );
        $io->writeln(
            sprintf('Low time: %d ms', min($times))
        );
        $io->writeln(
            sprintf('Max time: %d ms', max($times))
        );
        $io->writeln(
            sprintf(
                'Average memory: %d MB',
                array_sum($memories) / count($memories) / 1024 / 1024
            )
        );
    }
}
