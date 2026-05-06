<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use PsychedCms\MessageTemplate\Repository\MessageTemplateRepository;
use PsychedCms\MessageTemplate\State\MessageTemplatePreviewProcessor;
use PsychedCms\MessageTemplate\Translation\MessageTemplateTranslation;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MessageTemplateRepository::class)]
#[ORM\Table(name: 'message_templates')]
#[Gedmo\TranslationEntity(class: MessageTemplateTranslation::class)]
#[ApiResource(
    shortName: 'MessageTemplate',
    operations: [
        new GetCollection(
            uriTemplate: '/message-templates',
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            uriTemplate: '/message-templates/{key}',
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            uriTemplate: '/message-templates/{key}',
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Post(
            uriTemplate: '/message-templates/{key}/preview',
            input: false,
            output: false,
            processor: MessageTemplatePreviewProcessor::class,
            security: 'is_granted("ROLE_ADMIN")',
            read: false,
        ),
    ],
    normalizationContext: ['groups' => ['message_template:read']],
    denormalizationContext: ['groups' => ['message_template:write']],
)]
class MessageTemplate
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    #[ApiProperty(identifier: false, readable: false)]
    private ?Ulid $id = null;

    /**
     * Namespaced unique identifier ; e.g. `notification.set_modified`,
     * `email.welcome`. Acts as the API identifier — never edited.
     */
    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Assert\Regex(pattern: '/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/')]
    #[ApiProperty(identifier: true)]
    #[Groups(['message_template:read'])]
    private string $key = '';

    /**
     * The body, with `{placeholder}` markers. Localized via Gedmo translatable.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Gedmo\Translatable]
    #[Groups(['message_template:read', 'message_template:write'])]
    private string $body = '';

    /**
     * Admin-facing label describing the purpose of this template (translatable).
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Gedmo\Translatable]
    #[Groups(['message_template:read'])]
    private ?string $description = null;

    /**
     * Documentation list of placeholder names supported by this template
     * (without braces). Displayed in the admin UI to help editors.
     *
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['message_template:read'])]
    private array $placeholders = [];

    /**
     * Top-level grouping for the admin: `notification`, `email`, `sms`, …
     */
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['message_template:read'])]
    private string $category = 'notification';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['message_template:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'update')]
    #[Groups(['message_template:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, MessageTemplateTranslation> */
    #[ORM\OneToMany(targetEntity: MessageTemplateTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[Gedmo\Locale]
    private ?string $translatableLocale = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /** @return list<string> */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /** @param list<string> $placeholders */
    public function setPlaceholders(array $placeholders): static
    {
        $this->placeholders = $placeholders;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return Collection<int, MessageTemplateTranslation> */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function addTranslation(MessageTemplateTranslation $translation): static
    {
        if (!$this->translations->contains($translation)) {
            $this->translations->add($translation);
            $translation->setObject($this);
        }

        return $this;
    }

    public function removeTranslation(MessageTemplateTranslation $translation): static
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->translatableLocale = $locale;
    }
}
