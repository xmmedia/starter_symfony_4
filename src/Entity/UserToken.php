<?php

declare(strict_types=1);

namespace App\Entity;

use App\Projection\User\UserTokenFinder;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: UserTokenFinder::class)]
class UserToken implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    /**
     * @var \Ramsey\Uuid\Uuid
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private \Ramsey\Uuid\UuidInterface $userTokenId;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokens')]
    #[ORM\JoinColumn(referencedColumnName: 'user_id', nullable: false)]
    private User $user;

    public static function create(User $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): self
    {
        $token = new self();
        $token->userTokenId = \Ramsey\Uuid\Uuid::uuid4();
        $token->user = $user;
        $token->initialize($expiresAt, $selector, $hashedToken);

        return $token;
    }

    public function getId(): string
    {
        return $this->userTokenId->toString();
    }

    public function user(): User
    {
        return $this->user;
    }

    public function getUser(): User
    {
        return $this->user();
    }
}
