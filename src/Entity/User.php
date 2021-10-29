<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Serializer\Annotation\Groups;

//todo: mettre match à true

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="road_user")
 * @UniqueEntity(
 *      "email", 
 *      message="L'utilisateur existe déjà", 
 *      groups={"constraints_new", "constraints_edit_data"}
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(
     *      groups={"constraints_new", "constraints_edit"},
     *      message = "L'email ne doit pas être vide."
     * )
     * @Assert\Email(
     *      groups={"constraints_new", "constraints_edit"},
     *      message = "Le format de l'adresse mail est incorrect."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *      groups={"constraints_new", "constraints_edit_password"},
     *      message = "Le mot de passe ne doit pas être vide."
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit_password"},
     *      pattern = "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$^",
     *      match = false,
     *      message = "Votre mot de passe doit contenir au moins 1 majuscule et 1 minuscule et 1 chiffre et 1 caractère spécial et minimum 8 caractères.",
     * )
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit_password"},
     *      type="string"
     * )
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=100,
     *      maxMessage = "Le prénom doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[=%\$<>*+\}\{\\\/\]\[;()]]",
     *      match = false,
     *      message = "Le prénom ne doit pas contenir les caractères spéciaux suivants: = % $ < > * + } { \ / ] [ ; ( )"
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[\d]",
     *      match = false,
     *      message = "Le prénom ne doit pas contenir de chiffres ou de nombres."
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "Le prénom doit contenir au minimum un caractère alphabétique."
     * )
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit"},
     *      type="string"
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=100,
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[=%\$<>*+\}\{\\\/\]\[;()]]",
     *      match = false,
     *      message = "Le nom ne doit pas contenir les caractères spéciaux suivants: = % $ < > * + } { \ / ] [ ; ( )"
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[\d]",
     *      match = false,
     *      message = "Le nom ne doit pas contenir de chiffres ou de nombres."
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "Le nom doit contenir au minimum un caractère alphabétique."
     * )
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit"},
     *      type="string"
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      groups={"constraints_new", "constraints_edit_password"},
     *      message = "Le pseudo ne doit pas être vide."
     * )
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit_data"},
     *      max=100,
     *      maxMessage = "Le pseudo doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit_data"},
     *      pattern = "[[=%\$<>*+\}\{\\\/\]\[;()]]",
     *      match = false,
     *      message = "Le pseudo ne doit pas contenir les caractères spéciaux suivants: = % $ < > * + } { \ / ] [ ; ( )"
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit_data"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "Le pseudo doit contenir au minimum un caractère alphabétique."
     * )
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit_data"},
     *      type="string"
     * )
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max=3000,
     *      maxMessage = "La présentation doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "La présentation doit contenir au minimum un caractère alphabétique."
     * )
     */
    private $presentation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File(
     *     groups={"constraints_new", "constraints_edit"},
     *     maxSize = "1024k",
     *     maxSizeMessage = "L'avatar ne doit pas dépasser {{ limit }} Ko.",
     *     mimeTypes = {"image/*"},
     *     mimeTypesMessage = "Le format de l'avatar n'est pas supporté."
     * )
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File(
     *     groups={"constraints_new", "constraints_edit"},
     *     maxSize = "1024k",
     *     maxSizeMessage = "La couverture ne doit pas dépasser {{ limit }} Ko.",
     *     mimeTypes = {"image/*"},
     *     mimeTypesMessage = "Le format de la couverture n'est pas supporté."
     * )
     */
    private $cover;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('NOW');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setPresentation(?string $presentation): self
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
