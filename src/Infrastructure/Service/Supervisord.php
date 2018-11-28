<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use fXmlRpc\Client;
use fXmlRpc\Transport\HttpAdapterTransport;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Message\MessageFactory\DiactorosMessageFactory as MessageFactory;
use Supervisor\Connector\XmlRpc;
use Supervisor\Process as SupervisorProcess;
use Supervisor\Supervisor as SupervisorClient;

class Supervisord
{
    /** @var string */
    private $supervisordAddress;
    /** @var string */
    private $supervisordUsername;
    /** @var string */
    private $supervisordPassword;
    /** @var string */
    private $supervisordProgramPrefix;

    /** @var array Projections that are not run by supervisor */
    public static $notInSupervisor = [
    ];

    /** @var SupervisorClient */
    private static $supervisorClient;

    public function __construct(
        string $supervisordAddress,
        string $supervisordUsername,
        string $supervisordPassword,
        string $supervisordProgramPrefix
    ) {
        $this->supervisordAddress = $supervisordAddress;
        $this->supervisordUsername = $supervisordUsername;
        $this->supervisordPassword = $supervisordPassword;
        $this->supervisordProgramPrefix = $supervisordProgramPrefix;
    }

    public function client(): SupervisorClient
    {
        if (null !== self::$supervisorClient) {
            return self::$supervisorClient;
        }

        $guzzleClient = new \GuzzleHttp\Client(
            ['auth' => [$this->supervisordUsername, $this->supervisordPassword]]
        );

        $client = new Client(
            $this->supervisordAddress,
            new HttpAdapterTransport(
                new MessageFactory(),
                new GuzzleAdapter($guzzleClient)
            )
        );

        $connector = new XmlRpc($client);

        self::$supervisorClient = new SupervisorClient($connector);

        return self::$supervisorClient;
    }

    /**
     * Creates the Supervisor program name.
     */
    public function programName(string $projectionName): string
    {
        return sprintf(
            '%1$s:%1$s_%2$s',
            $this->supervisordProgramPrefix,
            $projectionName
        );
    }

    public function programPrefix(): string
    {
        return $this->supervisordProgramPrefix;
    }

    /**
     * Retrieves the Supervisor program/process.
     */
    public function projectionProcess(string $projectionName): SupervisorProcess
    {
        return $this->client()->getProcess($this->programName($projectionName));
    }

    /**
     * Checks if the Supervisor program/process is running.
     */
    public function isRunning(string $projectionName): bool
    {
        return $this->projectionProcess($projectionName)->isRunning();
    }

    /**
     * Stop the Supervisor program/process.
     */
    public function stop(string $projectionName): bool
    {
        return $this->client()->stopProcess($this->programName($projectionName));
    }

    /**
     * Start the Supervisor program/process.
     */
    public function start(string $projectionName): bool
    {
        return $this->client()->startProcess($this->programName($projectionName));
    }
}
