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
    /** @var ProjectionRunner */
    private $projectionRunner;

    /**
     * Event namespace to projections.
     *
     * @var array
     */
    private $namespaceToProjection = [
        'Xm\SymfonyBundle\Model\User\Event' => [
            'user_projection',
            'user_token_projection',
        ],
    ];

    public function __construct(ProjectionRunner $projectionRunner)
    {
        $this->projectionRunner = $projectionRunner;
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
        $className = \get_class($message);
        $namespace = substr($className, 0, strrpos($className, '\\'));

        if (!\array_key_exists($namespace, $this->namespaceToProjection)) {
            return [];
        }

        return $this->namespaceToProjection[$namespace];
    }
}
