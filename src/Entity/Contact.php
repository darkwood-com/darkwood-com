<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="contact")
 *
 * @ORM\Entity(repositoryClass="App\Repository\ContactRepository")
 */
class Contact
{
    use TimestampTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="contacts", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected ?\App\Entity\User $user = null;

    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    #[Assert\NotBlank(message: 'common.comment.required_email')]
    #[Assert\Email(message: '{{ value }} is invalid.', mode: 'strict')]
    private ?string $email = null;

    /**
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private ?string $website = null;

    /**
     * @ORM\Column(name="content", type="text")
     */
    #[Assert\NotBlank(message: 'common.comment.required_content')]
    private ?string $content = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $emailSent = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
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
     *
     * @return string
     */
    public function getEmail()
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
     *
     * @return string
     */
    public function getWebsite()
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
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set user.
     */
    public function setUser(User $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User
     */
    public function getUser()
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
