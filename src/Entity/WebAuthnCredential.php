<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebAuthnCredentialFinder;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Webauthn\PublicKeyCredentialSource;

#[ORM\Entity(repositoryClass: WebAuthnCredentialFinder::class)]
class WebAuthnCredential
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private \Ramsey\Uuid\UuidInterface $credentialId;

    /**
     * Base64-encoded binary credential ID, used for lookup by the bundle.
     * Stored as base64_encode(PublicKeyCredentialSource::$publicKeyCredentialId).
     */
    #[ORM\Column(length: 512, unique: true)]
    private string $publicKeyCredentialId;

    /** JSON-serialized PublicKeyCredentialSource. Deserialized via the repository's serializer. */
    #[ORM\Column(type: Types::TEXT)]
    private string $credentialSourceJson;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'passkeys')]
    #[ORM\JoinColumn(referencedColumnName: 'user_id', nullable: false)]
    private User $user;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    /**
     * @internal use WebAuthnCredentialFinder::createCredential() instead
     */
    public static function create(
        User $user,
        PublicKeyCredentialSource $source,
        string $credentialSourceJson,
        ?string $name,
    ): self {
        $credential = new self();
        $credential->credentialId = \Ramsey\Uuid\Uuid::uuid4();
        $credential->publicKeyCredentialId = base64_encode($source->publicKeyCredentialId);
        $credential->credentialSourceJson = $credentialSourceJson;
        $credential->user = $user;
        $credential->name = $name;
        $credential->createdAt = new \DateTimeImmutable();

        return $credential;
    }

    public function credentialId(): string
    {
        return $this->credentialId->toString();
    }

    public function publicKeyCredentialId(): string
    {
        return $this->publicKeyCredentialId;
    }

    public function credentialSourceJson(): string
    {
        return $this->credentialSourceJson;
    }

    public function updateCredentialSource(string $credentialSourceJson): void
    {
        $this->credentialSourceJson = $credentialSourceJson;
        $this->lastUsedAt = new \DateTimeImmutable();
    }

    public function user(): User
    {
        return $this->user;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function lastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }
}
