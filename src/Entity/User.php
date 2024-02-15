<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserData;
use App\Model\User\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\StringUtil;

#[ORM\Entity(repositoryClass: \App\Projection\User\UserFinder::class, readOnly: true)]
class User implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var \Ramsey\Uuid\Uuid
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private \Ramsey\Uuid\UuidInterface $userId;

    #[ORM\Column(length: 150, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column]
    private bool $verified = false;

    #[ORM\Column]
    private bool $active = false;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLogin = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $loginCount = 0;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $userData = null;

    /**
     * @var UserToken[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserToken::class)]
    private array|Collection $tokens;

    public function __construct()
    {
        $this->tokens = new ArrayCollection();
    }

    public function userId(): UserId
    {
        return UserId::fromUuid($this->userId);
    }

    public function email(): Email
    {
        return Email::fromString($this->email, $this->name());
    }

    public function getUserIdentifier(): string
    {
        return $this->email()->toString();
    }

    public function password(): string
    {
        return $this->password;
    }

    /**
     * Allows setting the password when it changes while the user is logged in,
     * for example when their password is upgraded.
     */
    public function upgradePassword(string $hashedPassword): void
    {
        $this->password = $hashedPassword;
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
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = Role::ROLE_USER()->getValue();

        return array_unique($roles);
    }

    public function getRoles(): array
    {
        return $this->roles();
    }

    public function firstRole(): Role
    {
        return Role::byValue($this->roles[0]);
    }

    public function lastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
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

    public function name(): ?string
    {
        return StringUtil::trim(
            sprintf('%s %s', $this->firstName, $this->lastName),
        );
    }

    public function userData(): ?UserData
    {
        if (null === $this->userData) {
            return null;
        }

        return UserData::fromArray($this->userData);
    }

    /**
     * User will be logged out if their email (username) or password changes,
     * if they've become inactive or unverified, or if their roles have changed.
     *
     * @param User|UserInterface $user the user just loaded from the db
     */
    public function isEqualTo(self|UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->password() !== $user->password()) {
            return false;
        }

        if (!$this->email()->sameValueAs($user->email())) {
            return false;
        }

        // basically the same as above normally, but just in case
        if ($this->getUserIdentifier() !== $user->getUserIdentifier()) {
            return false;
        }

        if (!$user->active) {
            return false;
        }

        if (!$user->verified) {
            return false;
        }

        // check if roles have changed
        $currentRoles = array_map('strval', $this->roles());
        $newRoles = array_map('strval', $user->roles());
        $rolesChanged = \count($currentRoles) !== \count($newRoles) || \count($currentRoles) !== \count(array_intersect($currentRoles, $newRoles));

        return !$rolesChanged;
    }
}
