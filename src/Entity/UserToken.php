<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\User\Token;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Projection\User\UserTokenFinder")
 */
class UserToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=50)
     */
    private string $token;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tokens")
     * @ORM\JoinColumn(referencedColumnName="user_id", nullable=false)
     */
    private User $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeImmutable $generatedAt;

    public function token(): Token
    {
        return Token::fromString($this->token);
    }

    public function user(): User
    {
        return $this->user;
    }

    public function generatedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }
}
