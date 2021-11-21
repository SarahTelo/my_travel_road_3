<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\Table(name="road_category")
 * @UniqueEntity(
 *      "name", 
 *      message="La catégorie existe déjà", 
 *      groups={"constraints_new", "constraints_edit"}
 * )
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *      "category_list_admin", "category_list_public",
     *      "category_travel_detail_admin", "category_travel_detail",
     *      "travel_list_admin", "travel_list_public", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     *      "home_detail",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      groups={"constraints_new", "constraints_edit"},
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
     *      "category_list_admin", "category_list_public",
     *      "category_travel_detail_admin", "category_travel_detail",
     *      "travel_list_admin", "travel_list_public", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     *      "home_detail",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({
     *      "category_travel_detail_admin", "category_list_admin",
     * })
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "category_travel_detail_admin", "category_list_admin",
     * })
     */
    private $updated_at;

    /**
     * @ORM\ManyToMany(targetEntity=Travel::class, mappedBy="categories")
     * @Groups({
     *      "category_travel_detail_admin", "category_travel_detail",
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
        if (!$this->travels->contains($travel)) {
            $this->travels[] = $travel;
            $travel->addCategory($this);
        }

        return $this;
    }

    public function removeTravel(Travel $travel): self
    {
        if ($this->travels->removeElement($travel)) {
            $travel->removeCategory($this);
        }

        return $this;
    }
}
