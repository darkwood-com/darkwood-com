<?php

namespace App\Entity;

use App\Entity\Game\Player;
use App\Entity\Traits\TimestampTrait;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("email", message="The email '{{ value }}' is already used.")
 * @UniqueEntity("username", message="The username '{{ value }}' is already taken.")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class User implements \Symfony\Component\Security\Core\User\UserInterface, \Serializable, \Stringable
{
    use TimestampTrait;
    /**
     * Id.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 32,
     *      minMessage = "The username must be at least {{ limit }} characters long",
     *      maxMessage = "The username cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;
    /**
     * @Assert\NotBlank(
     *     message="form.general.required"
     * )
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     mode = "strict"
     * )
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    protected $email;
    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];
    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;
    /**
     * Civility.
     *
     * @ORM\Column(length=255, nullable=true)
     */
    protected $civility = 'm';
    /**
     * Firstname.
     *
     * @Assert\Length(
     *     min="2",
     *     max="255",
     *     minMessage="form.general.short",
     *     maxMessage="form.general.long"
     * )
     * @ORM\Column(length=255, nullable=true)
     */
    protected $firstname;
    /**
     * Lastname.
     *
     * @Assert\Length(
     *     min="2",
     *     max="255",
     *     minMessage="form.general.short",
     *     maxMessage="form.general.long"
     * )
     * @ORM\Column(length=255, nullable=true)
     */
    protected $lastname;
    /**
     * Edit.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $birthday;
    /**
     * City.
     *
     * @ORM\Column(length=255, nullable=true)
     */
    protected $city;
    /**
     * Comment.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;
    /**
     * Players.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Game\Player", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $player;
    /**
     * @var File
     *
     * @Vich\UploadableField(mapping="users", fileNameProperty="imageName")
     */
    protected $image;
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $imageName;
    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebookId;
    /**
     * Comments.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $comments;
    /**
     * Contacts.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Contact", mappedBy="user", cascade={"persist", "remove"})
     */
    protected $contacts;
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $emailSent;
    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;
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
    /**
     * @param mixed $email
     */
    public function setEmail($email)
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
     *
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
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
     *
     * @param mixed $lastname
     */
    public function setLastname($lastname)
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
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }
    /**
     * @return \DateTime
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
    /**
     * @param mixed $city
     */
    public function setCity($city)
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
    /**
     * @param mixed $comment
     */
    public function setComment($comment)
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
            $this->updated = new \DateTime('now');
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
    /**
     * @param mixed $civility
     */
    public function setCivility($civility)
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
     *
     * @param \App\Entity\Game\Player $player
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
    /**
     * String representation of object
     *
     * @see http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     *
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([$this->id, $this->username, $this->email, $this->password]);
    }
    /**
     * Constructs the object
     *
     * @see http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     *
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->username, $this->email, $this->password, ) = unserialize($serialized, ['allowed_classes' => false]);
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
