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
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=180)
     */
    private $token;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tokens")
     * @ORM\JoinColumn(referencedColumnName="user_id", nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $generatedAt;

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
        return \DateTimeImmutable::createFromMutable($this->generatedAt);
    }
}
