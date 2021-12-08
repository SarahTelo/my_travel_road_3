<?php

namespace App\Entity;

use App\Repository\StepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=StepRepository::class)
 * @ORM\Table(name="road_step")
 */
class Step
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
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
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     *      "travel_detail_private", "travel_detail_public",
     *      "image_list", 
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(
     *      groups={"constraints_new", "constraints_edit"},
     *      type="integer",
     *      message = "Le numéro d'étape n'a pas le bon format."
     * )
     * @Assert\PositiveOrZero(
     *      groups={"constraints_new", "constraints_edit"},
     *      message = "Le numéro d'étape doit être supérieur ou égal 0."
     * )
     * @Groups({
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     *      "travel_detail_admin", "travel_detail_private", "travel_detail_public",
     * })
     */
    private $sequence;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public",
     *      "image_list", 
     * })
     */
    private $cover;

    /**
     * @ORM\Column(type="text", length=10000, nullable=true)
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
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     * })
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex(
     *      groups={"constraints_new", "constraints_edit"},
     *      pattern = "[[0-9]]",
     *      match = true,
     *      message = "Les coordonnées doivent contenir au minimum un caractère numérique."
     * )
     * @Groups({
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     *      "travel_detail_private", "travel_detail_public",
     * })
     */
    private $start_coordinate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "step_list_admin", "step_list_private", "step_list_public",
     *      "step_detail_admin", "step_detail_private", "step_detail_public", 
     *      "travel_detail_private", "travel_detail_public",
     * })
     */
    private $start_at;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({
     *      "step_list_admin",
     *      "step_detail_admin",
     * })
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "step_list_admin",
     *      "step_detail_admin",
     * })
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Travel::class, inversedBy="steps")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({
     *      "image_list", "image_list_admin",
     * })
     */
    private $travel;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="step")
     * @Groups({
     *      "step_detail_private", "step_detail_public",
     * })
     */
    private $images;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('NOW');
        $this->images = new ArrayCollection();
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

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): self
    {
        $this->sequence = $sequence;

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

    public function getStartCoordinate(): ?string
    {
        return $this->start_coordinate;
    }

    public function setStartCoordinate(?string $start_coordinate): self
    {
        $this->start_coordinate = $start_coordinate;

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

    public function getTravel(): ?Travel
    {
        return $this->travel;
    }

    public function setTravel(?Travel $travel): self
    {
        $this->travel = $travel;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setStep($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getStep() === $this) {
                $image->setStep(null);
            }
        }

        return $this;
    }
}
