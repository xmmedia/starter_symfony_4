<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * The user's password hash.
 *
 * Deliberately kept out of the event stream & the user projection: a hash is
 * authentication state, not domain history, so it's stored once & overwritten
 * in place rather than accumulating forever in an append-only store.
 * This table is not owned by a projection & is not rebuilt by a replay.
 */
#[ORM\Entity(readOnly: true)]
class UserCredential
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'credential')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private User $user;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function user(): User
    {
        return $this->user;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Allows setting the password when it changes while the user is logged in,
     * for example when their password is upgraded.
     */
    public function upgradePassword(#[\SensitiveParameter] string $hashedPassword): void
    {
        $this->password = $hashedPassword;
    }
}
