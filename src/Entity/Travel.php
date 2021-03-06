<?php

namespace App\Entity;

use App\Repository\TravelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TravelRepository::class)
 * @ORM\Table(name="road_travel")
 */
class Travel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *      "travel_list_public", 
     *      "travel_list_admin", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     *      "home_detail",
     *      "category_travel_detail_admin", "category_travel_detail",
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public",
     *      "image_list", "image_list_admin",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "Le titre ne doit pas être vide."
     * )
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=255,
     *      maxMessage = "Le titre doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "Le titre doit contenir au minimum un caractère alphabétique."
     * )
     * @Groups({
     *      "travel_list_public",
     *      "travel_list_admin",
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     *      "home_detail",
     *      "category_travel_detail_admin", "category_travel_detail",
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public",
     *      "image_list", "image_list_admin",
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *      "travel_list_public", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     *      "home_detail",
     *      "category_travel_detail",
     *      "step_list_private", "step_list_public",
     *      "image_list", "step_detail_admin", "step_detail_private", "step_detail_public", 
     * })
     */
    private $cover;

    /**
     * @ORM\Column(type="string", length=10000, nullable=true)
     * @Assert\Length(
     *      groups={"constraints_new", "constraints_edit"},
     *      max=3000,
     *      maxMessage = "La description doit contenir au maximum {{ limit }} caractères.",
     * )
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[a-zA-Z]]",
     *      match = true,
     *      message = "La description doit contenir au minimum un caractère alphabétique."
     * )
     * @Groups({
     *      "travel_list_public", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     * })
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     * })
     */
    private $start_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     * })
     */
    private $end_at;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "Le statut ne doit pas être vide."
     * )
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit"},
     *      type="integer",
     *      message = "Le statut n'a pas le bon format."
     * )
     * @Assert\PositiveOrZero(
     *      groups={"constraints_new", "constraints_edit"},
     *      message = "Le statut doit avoir pour valeur 0, 1 ou 2."
     * )
     * @Groups({
     *      "travel_list_public", 
     *      "travel_list_admin", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     *      "travel_list_private",
     *      "home_detail",
     *      "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     * })
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank(
     *      groups={"constraints_new"},
     *      message = "La visibilité ne doit pas être vide."
     * )
     * @Groups({
     *      "travel_list_admin",
     *      "travel_detail_admin", "travel_detail_private",
     *      "travel_list_private",
     *      "step_list_private", "step_list_public",
     * })
     */
    private $visibility;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({
     *      "travel_detail_admin", "travel_list_admin",
     * })
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "travel_detail_admin", "travel_list_admin",
     * })
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="travel")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({
     *      "travel_list_admin", "travel_list_public",
     *      "travel_detail_admin", "travel_detail_public",
     *      "home_detail",
     *      "category_travel_detail_admin", "category_travel_detail",
     *      "image_list", 
     * })
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="travels")
     * @Groups({
     *      "travel_list_admin", "travel_list_public", 
     *      "travel_detail_public",
     *      "travel_detail_admin", "travel_list_private", "travel_detail_private",
     *      "home_detail",
     * })
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=Step::class, mappedBy="travel", orphanRemoval=true)
     * @Groups({
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     * })
     */
    private $steps;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('NOW');
        $this->status = 2;
        $this->visibility = 0;
        $this->categories = new ArrayCollection();
        $this->steps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->start_at;
    }

    public function setStartAt(?\DateTimeInterface $start_at): self
    {
        $this->start_at = $start_at;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->end_at;
    }

    public function setEndAt(?\DateTimeInterface $end_at): self
    {
        $this->end_at = $end_at;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): self
    {
        $this->visibility = $visibility;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection|Step[]
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps[] = $step;
            $step->setTravel($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->removeElement($step)) {
            // set the owning side to null (unless already changed)
            if ($step->getTravel() === $this) {
                $step->setTravel(null);
            }
        }

        return $this;
    }
}
