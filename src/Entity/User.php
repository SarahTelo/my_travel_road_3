<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
 *      groups={"constraints_new", "constraints_edit"}
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *      "user_list", "user_list_admin", "user_profile", "user_detail",
     *      "travel_list_admin", "travel_list_public",
     *      "travel_detail_public",
     *      "home_detail",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "L'email ne doit pas être vide."
     * )
     * @Assert\Email(
     *      groups={"constraints_new", "constraints_edit"},
     *      message = "Le format de l'adresse mail est incorrect."
     * )
     * @Groups({
     *      "user_list_admin", "user_profile",
     * })
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({
     *      "user_list_admin",
     * })
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
     * @Groups({
     *      "user_list_admin", "user_profile",
     * })
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
     * @Groups({
     *      "user_list_admin", "user_profile",
     * })
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "Le pseudo ne doit pas être vide."
     * )
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=100,
     *      maxMessage = "Le pseudo doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[=%\$<>*+\}\{\\\/\]\[;()]]",
     *      match = false,
     *      message = "Le pseudo ne doit pas contenir les caractères spéciaux suivants: = % $ < > * + } { \ / ] [ ; ( )"
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "Le pseudo doit contenir au minimum un caractère alphabétique."
     * )
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit"},
     *      type="string"
     * )
     * @Groups({
     *      "user_list", "user_list_admin", "user_profile", "user_detail",
     *      "travel_list_public",
     *      "travel_detail_public",
     *      "home_detail",
     * })
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=10000, nullable=true)
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=3000,
     *      maxMessage = "La présentation doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "La présentation doit contenir au minimum un caractère alphabétique."
     * )
     * @Groups({
     *      "user_list", "user_list_admin", "user_profile", "user_detail",
     *      "travel_detail_public",
     * })
     */
    private $presentation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *      "user_list", "user_list_admin", "user_profile", "user_detail",
     *      "travel_list_public",
     *      "travel_detail_public",
     *      "home_detail",
     * })
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *      "user_list_admin", "user_profile", "user_detail",
     * })
     */
    private $cover;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({
     *      "user_list_admin",
     * })
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "user_list_admin",
     * })
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=Travel::class, mappedBy="user")
     * @Groups({
     *      "user_detail_à-retirer!",
     * })
     */
    private $travels;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('NOW');
        $this->travels = new ArrayCollection();
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

    /**
     * @return Collection|Travel[]
     */
    public function getTravels(): Collection
    {
        return $this->travels;
    }

    public function addTravel(Travel $travel): self
    {
        if (!$this->travel->contains($travel)) {
            $this->travel[] = $travel;
            $travel->setUser($this);
        }

        return $this;
    }

    public function removeTravel(Travel $travel): self
    {
        if ($this->travel->removeElement($travel)) {
            // set the owning side to null (unless already changed)
            if ($travel->getUser() === $this) {
                $travel->setUser(null);
            }
        }

        return $this;
    }
}
