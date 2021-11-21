<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CountryRepository::class)
 * @ORM\Table(name="road_country")
 * @UniqueEntity(
 *      "name", 
 *      message="Le pays existe déjà", 
 *      groups={"constraints_new", "constraints_edit"}
 * )
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *      "country_list_admin", "country_list_public",
     *      "country_user_detail_admin", "country_user_detail",
     *      "home_detail",
     *      "user_list_admin",
     *      "user_detail", "user_profile",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "Le nom ne doit pas être vide."
     * )
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=255,
     *      maxMessage = "Le nom doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "Le nom doit contenir au minimum un caractère alphabétique."
     * )
     * @Groups({
     *      "country_list_admin", "country_list_public",
     *      "country_user_detail_admin", "country_user_detail",
     *      "home_detail",
     *      "user_list_admin",
     *      "user_detail",
     *      "user_profile",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "Les coordonées ne doivent pas être vide."
     * )
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=255,
     *      maxMessage = "Les coordonées doivent contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[0-9]]",
     *      match = true,
     *      message = "Les coordonées doivent contenir au minimum un chiffre."
     * )
     * @Groups({
     *      "country_list_admin", "country_list_public",
     *      "country_user_detail_admin", "country_user_detail",
     *      "home_detail",
     * })
     */
    private $coordinate;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({
     *      "country_list_admin",
     * })
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "country_list_admin",
     * })
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="country")
     * @Groups({
     *      "country_user_detail_admin", "country_user_detail",
     * })
     */
    private $users;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('NOW');
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCoordinate(): ?string
    {
        return $this->coordinate;
    }

    public function setCoordinate(?string $coordinate): self
    {
        $this->coordinate = $coordinate;

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
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCountry($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCountry() === $this) {
                $user->setCountry(null);
            }
        }

        return $this;
    }
}
