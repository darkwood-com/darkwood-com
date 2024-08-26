<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\Table(name: 'contact')]
class Contact
{
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contacts', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    protected ?User $user = null;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'common.comment.required_email')]
    #[Assert\Email(message: '{{ value }} is invalid.', mode: 'strict')]
    #[ORM\Column(name: 'email', type: Types::STRING, length: 255)]
    private ?string $email = null;

    #[ORM\Column(name: 'website', type: Types::STRING, length: 255, nullable: true)]
    private ?string $website = null;

    #[Assert\NotBlank(message: 'common.comment.required_content')]
    #[ORM\Column(name: 'content', type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $emailSent = null;

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set email.
     *
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * Get email.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set website.
     *
     * @param string $website
     */
    public function setWebsite($website): void
    {
        $this->website = $website;
    }

    /**
     * Get website.
     */
    public function getWebsite(): string
    {
        return $this->website;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * Get content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set user.
     */
    public function setUser(?User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Get user.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getEmailSent(): ?bool
    {
        return $this->emailSent;
    }

    public function setEmailSent(bool $emailSent): self
    {
        $this->emailSent = $emailSent;

        return $this;
    }
}
