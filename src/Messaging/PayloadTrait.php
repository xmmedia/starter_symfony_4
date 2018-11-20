<?php

declare(strict_types=1);

namespace App\Messaging;

/**
 * Use this trait together with the PayloadConstructable interface
 * to use simple message instantiation and default implementations
 * for DomainMessage::payload() and DomainMessage::setPayload().
 *
 * @see https://github.com/prooph/common
 */
trait PayloadTrait
{
    /**
     * @var array
     */
    protected $payload;

    public function __construct(array $payload = [])
    {
        $this->init();
        $this->setPayload($payload);
    }

    public function payload(): array
    {
        return $this->payload;
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * Use this method to initialize message with defaults or extend your class from DomainMessage.
     */
    abstract protected function init(): void;
}
