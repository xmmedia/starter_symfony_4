<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\Email;
use App\Model\User\Name;
use App\Model\User\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, EncoderAwareInterface
{
    /**
     * @var \Ramsey\Uuid\Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @var Email|string
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $verified;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $confirmationToken;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $loginCount = 0;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $firstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $lastName;

    public function id(): UserId
    {
        return UserId::fromUuid($this->id);
    }

    public function email(): Email
    {
        return Email::fromString($this->email);
    }

    public function getUsername(): string
    {
        return $this->email()->toString();
    }

    public function password(): string
    {
        return $this->password;
    }

    public function getPassword(): string
    {
        return $this->password();
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        // nothing atm, object never stored un-encoded password
    }

    public function verified(): bool
    {
        return $this->verified;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function roles(): array
    {
        return $this->roles;
    }

    public function getRoles(): array
    {
        return $this->roles();
    }

    public function confirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function passwordRequestedAt(): ?\DateTimeImmutable
    {
        if (null === $this->passwordRequestedAt) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable($this->passwordRequestedAt);
    }

    public function lastLogin(): ?\DateTimeImmutable
    {
        if (null === $this->lastLogin) {
            return null;
        }

        return \DateTimeImmutable::createFromMutable($this->lastLogin);
    }

    public function loginCount(): int
    {
        return $this->loginCount;
    }

    public function firstName(): ?Name
    {
        if (null === $this->firstName) {
            return null;
        }

        return Name::fromString($this->firstName);
    }

    public function lastName(): ?Name
    {
        if (null === $this->lastName) {
            return null;
        }

        return Name::fromString($this->lastName);
    }

    public function name(): string
    {
        return trim(sprintf('%s %s', $this->firstName, $this->lastName));
    }

    public function getEncoderName()
    {
        foreach (['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'] as $role) {
            if (in_array($role, $this->roles, true)) {
                return 'harsh';
            }
        }

        return null;
    }
}
