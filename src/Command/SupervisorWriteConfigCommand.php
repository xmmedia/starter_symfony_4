<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

final class SupervisorWriteConfigCommand extends Command
{
    /** @var string */
    private $kernelProjectDir;

    /** @var string */
    private $kernelEnv;

    /** @var string */
    private $logDir;

    /** @var string */
    private $supervisordProgramPrefix;

    public function __construct(
        string $kernelProjectDir,
        string $kernelEnv,
        string $logDir,
        string $supervisordProgramPrefix
    ) {
        parent::__construct();

        $this->kernelProjectDir = $kernelProjectDir;
        $this->kernelEnv = $kernelEnv;
        $this->logDir = $logDir;
        $this->supervisordProgramPrefix = $supervisordProgramPrefix;
    }

    protected function configure()
    {
        $this
            ->setName('app:supervisor:write-config')
            ->setDescription('Writes the config for supervisord.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Writing Supervisord Config');

        $config = Yaml::parseFile($this->kernelProjectDir.'/config/supervisord.yaml');

        Assert::keyExists($config, 'programs', 'The programs key is required in the config file.');

        $outputPath = getenv('HOME').'/supervisord.conf';
        $user = posix_getpwuid(posix_geteuid())['name'];
        $programs = 0;

        // 1 - program name
        // 2 - command
        // 3 - current dir path
        // 4 - current user
        // 5 - path to log dir
        // 6 - symfony env
        // 7 - supervisord program prefix
        $programConfigBase = <<<EOA
[program:%7\$s_%1\$s]
command=%2\$s
directory=%3\$s
user=%4\$s
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=%5\$s/%1\$s.log
EOA;

        $programNames = [];
        $programConfigs = [];
        foreach ($config['programs'] as $name => $programConfig) {
            $io->writeln('Adding: '.$name);
            $programNames[] = $this->supervisordProgramPrefix.'_'.$name;

            $programConfigs[] = sprintf(
                $programConfigBase,
                $name, // 1
                $programConfig['command'], // 2
                getenv('PWD'), // 3
                $user, // 4
                $this->logDir.'/supervisord', // 5
                $this->kernelEnv, // 6
                $this->supervisordProgramPrefix // 7
            );

            ++$programs;
        }

        $programsStr = implode(',', $programNames);
        $groupConfig = <<<EOA
; {$this->supervisordProgramPrefix} supervisord config

[group:{$this->supervisordProgramPrefix}]
programs=$programsStr
EOA;

        $programConfigStr = implode("\n\n", $programConfigs);

        $fileSystem = new Filesystem();

        $fileSystem->remove($outputPath);
        $fileSystem->dumpFile($outputPath, $groupConfig."\n\n".$programConfigStr);

        $io->success(sprintf('Written to %s for %d programs', $outputPath, $programs));
    }
}
