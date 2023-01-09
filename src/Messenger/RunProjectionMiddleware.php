<?php

declare(strict_types=1);

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Infrastructure\Service\ProjectionRunner;

class RunProjectionMiddleware implements MiddlewareInterface
{
    private const ENQUIRY = 'enquiry_projection';
    private const USER = 'user_projection';
    private const USER_TOKEN = 'user_token_projection';

    /**
     * Event namespace to projections.
     */
    private array $namespaceToProjection = [
        'App\Model\Enquiry\Event' => [
            self::ENQUIRY,
        ],
        'App\Model\User\Event'    => [
            self::USER,
            self::USER_TOKEN,
        ],
    ];

    public function __construct(private ProjectionRunner $projectionRunner)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof AggregateChanged) {
            if ($projectionNames = $this->getProjectionName($message)) {
                foreach ($projectionNames as $projectionName) {
                    $this->projectionRunner->run($projectionName);
                }
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function getProjectionName(AggregateChanged $message): array
    {
        $class = new \ReflectionClass($message);

        if (!$class->inNamespace()) {
            return [];
        }

        if (!\array_key_exists($class->getNamespaceName(), $this->namespaceToProjection)) {
            return [];
        }

        return $this->namespaceToProjection[$class->getNamespaceName()];
    }
}
