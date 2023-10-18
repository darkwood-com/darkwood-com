<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Game\Player;
use App\Entity\Traits\TimestampTrait;
use App\Repository\UserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
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
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, unique: true, nullable: false)]
    protected string $email;

    #[ORM\Column(length: 255, nullable: true)]
    protected $civility = 'm';

    #[Assert\Length(min: 2, max: 255, minMessage: 'form.general.short', maxMessage: 'form.general.long')]
    #[ORM\Column(length: 255, nullable: true)]
    protected $firstname;

    #[Assert\Length(min: 2, max: 255, minMessage: 'form.general.short', maxMessage: 'form.general.long')]
    #[ORM\Column(length: 255, nullable: true)]
    protected $lastname;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $birthday = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected $city;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    protected ?string $comment = null;

    #[ORM\OneToOne(targetEntity: \App\Entity\Game\Player::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected ?\App\Entity\Game\Player $player = null;

    /**
     * @var File
     */
	#[Vich\UploadableField(mapping: 'users', fileNameProperty: 'imageName')]
    protected $image;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $imageName = null;

    #[ORM\Column(name: 'facebook_id', type: \Doctrine\DBAL\Types\Types::STRING, length: 255, nullable: true)]
    protected ?string $facebookId = null;

    /**
     * Comments.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Comment>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Comment::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $comments;

    /**
     * Contacts.
     *
     * @var \Doctrine\Common\Collections\Collection<\App\Entity\Contact>
     */
    #[ORM\OneToMany(targetEntity: \App\Entity\Contact::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected \Doctrine\Common\Collections\Collection $contacts;

    /**
     * Id.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    #[Assert\Length(min: 2, max: 32, minMessage: 'The username must be at least {{ limit }} characters long', maxMessage: 'The username cannot be longer than {{ limit }} characters')]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::JSON)]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN, nullable: true)]
    private ?bool $emailSent = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::BOOLEAN)]
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
     * @return string the string representation of the object or null
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

    /**
     * @return mixed
     */
    public function getEmail()
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
        return $this->id;
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
     *
     * @return mixed
     */
    public function getFirstname()
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
     *
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    public function setCity(mixed $city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function setComment(mixed $comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $image
     */
    public function setImage(File $image)
    {
        $this->image = $image;
        if ($image) {
            // doctrine listeners event
            $this->updated = new DateTime('now');
        }
    }

    /**
     * @return string
     */
    public function getImageName()
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

    /**
     * @return string
     */
    public function getFacebookId()
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

    /**
     * @return mixed
     */
    public function getCivility()
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
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
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
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set player.
     */
    public function setPlayer(Player $player = null): void
    {
        $this->player = $player;
    }

    /**
     * Get player.
     *
     * @return \App\Entity\Game\Player
     */
    public function getPlayer()
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
    public function eraseCredentials()
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
