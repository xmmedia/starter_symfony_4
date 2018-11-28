<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\Cloudflare;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PurgeCloudflareCacheCommand extends Command
{
    /** @var Cloudflare */
    private $cloudflare;

    public function __construct(Cloudflare $cloudflare)
    {
        parent::__construct();

        $this->cloudflare = $cloudflare;
    }

    protected function configure()
    {
        $this
            ->setName('app:cloudflare:purge-cache')
            ->setDescription('Purges the entire Cloudflare cache.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Purge Cloudflare Cache');

        if (!$this->cloudflare->clearCache()) {
            $io->error('Failed to clear the cache.');
        }

        $io->success('Cache Purged');
    }
}
