<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\AuthLog\AuthLogId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Projection\AuthLog\AuthLogFinder::class, readOnly: true)]
class AuthLog
{
    /**
     * @var \Ramsey\Uuid\Uuid
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private \Ramsey\Uuid\UuidInterface $authLogId;

    #[ORM\Column(length: 50)]
    private string $eventType;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'authLogs')]
    #[ORM\JoinColumn(referencedColumnName: 'user_id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(referencedColumnName: 'user_id', nullable: true)]
    private ?User $impersonatedUser = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 45)]
    private string $ipAddress;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $route = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $occurredAt;

    public function authLogId(): AuthLogId
    {
        return AuthLogId::fromUuid($this->authLogId);
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function impersonatedUser(): ?User
    {
        return $this->impersonatedUser;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function ipAddress(): string
    {
        return $this->ipAddress;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function route(): ?string
    {
        return $this->route;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
