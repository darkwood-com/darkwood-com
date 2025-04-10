<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Game\Player;
use App\Entity\Traits\TimestampTrait;
use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[UniqueEntity('email', message: "The email '{{ value }}' is already used.")]
#[UniqueEntity('username', message: "The username '{{ value }}' is already taken.")]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user')]
class User implements UserInterface, Stringable, PasswordAuthenticatedUserInterface
{
    use TimestampTrait;

    #[Assert\NotBlank(message: 'form.general.required')]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.", mode: 'strict')]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    protected string $email;

    #[ORM\Column(length: 255, nullable: true)]
    protected $civility = 'm';

    #[Assert\Length(min: 2, max: 255, minMessage: 'form.general.short', maxMessage: 'form.general.long')]
    #[ORM\Column(length: 255, nullable: true)]
    protected $firstname;

    #[Assert\Length(min: 2, max: 255, minMessage: 'form.general.short', maxMessage: 'form.general.long')]
    #[ORM\Column(length: 255, nullable: true)]
    protected $lastname;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $birthday = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected $city;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $comment = null;

    #[ORM\OneToOne(targetEntity: Player::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected ?Player $player = null;

    /**
     * @var File
     */
    #[Vich\UploadableField(mapping: 'users', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    #[ORM\Column(name: 'facebook_id', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $facebookId = null;

    /**
     * Comments.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected Collection $comments;

    /**
     * Contacts.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Contact>
     */
    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected Collection $contacts;

    /**
     * Id.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Assert\Length(min: 2, max: 32, minMessage: 'The username must be at least {{ limit }} characters long', maxMessage: 'The username cannot be longer than {{ limit }} characters')]
    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $emailSent = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isVerified = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * String representation of object.
     *
     * @see http://php.net/manual/en/serializable.serialize.php
     *
     * @return array the string representation of the object or null
     *
     * @since 5.1.0
     */
    public function __serialize(): array
    {
        return [$this->id, $this->username, $this->email, $this->password];
    }

    /**
     * Constructs the object.
     *
     * @see http://php.net/manual/en/serializable.unserialize.php
     * @since 5.1.0
     */
    public function __unserialize(array $data): void
    {
        [$this->id, $this->username, $this->email, $this->password] = $data;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername($username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): mixed
    {
        return $this->email;
    }

    public function setEmail(mixed $email)
    {
        $this->email = $email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set firstname.
     */
    public function setFirstname(mixed $firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * Get firstname.
     */
    public function getFirstname(): mixed
    {
        return $this->firstname;
    }

    /**
     * Set lastname.
     */
    public function setLastname(mixed $lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * Get lastname.
     */
    public function getLastname(): mixed
    {
        return $this->lastname;
    }

    /**
     * @param DateTimeInterface|null $birthday
     */
    public function setBirthday(?DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function getCity(): mixed
    {
        return $this->city;
    }

    public function setCity(mixed $city)
    {
        $this->city = $city;
    }

    public function getComment(): mixed
    {
        return $this->comment;
    }

    public function setComment(mixed $comment)
    {
        $this->comment = $comment;
    }

    public function getImage(): mixed
    {
        return $this->image;
    }

    /**
     * @param File|UploadedFile $image
     */
    public function setImage(File $image)
    {
        $this->image = $image;
        if ($image) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    public function getFacebookId(): string
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    public function setCivility(mixed $civility)
    {
        $this->civility = $civility;
    }

    public function getCivility(): mixed
    {
        return $this->civility;
    }

    /**
     * Add comment.
     */
    public function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
        $comment->setUser($this);
    }

    /**
     * Remove comment.
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
        $comment->setUser(null);
    }

    /**
     * Get comments.
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Add contact.
     */
    public function addContact(Contact $contact): void
    {
        $this->contacts[] = $contact;
        $contact->setUser($this);
    }

    /**
     * Remove contact.
     */
    public function removeContact(Contact $contact)
    {
        $this->contacts->removeElement($contact);
        $contact->setUser(null);
    }

    /**
     * Get contacts.
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    /**
     * Set player.
     */
    public function setPlayer(?Player $player = null): void
    {
        $this->player = $player;
    }

    /**
     * Get player.
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
