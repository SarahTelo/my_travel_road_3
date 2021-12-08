<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @ORM\Table(name="road_image")
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({
     *      "image_list", "image_list_admin",
     *      "step_detail_private", "step_detail_public",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *      "image_list", "image_list_admin",
     *      "step_detail_private", "step_detail_public",
     * })
     */
    private $path;

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
     *      "image_list", "image_list_admin",
     *      "step_detail_private", "step_detail_public",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
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
     *      "image_list", "image_list_admin",
     *      "step_detail_private", "step_detail_public",
     * })
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "image_list", "image_list_admin",
     *      "step_detail_private", "step_detail_public",
     * })
     */
    private $taken_at;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({
     *      "image_list_admin",
     * })
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({
     *      "image_list_admin",
     * })
     */
    private $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity=Step::class, inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({
     *      "image_list", "image_list_admin",
     * })
     */
    private $step;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable('NOW');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTakenAt(): ?\DateTimeInterface
    {
        return $this->taken_at;
    }

    public function setTakenAt(?\DateTimeInterface $taken_at): self
    {
        $this->taken_at = $taken_at;

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

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(?Step $step): self
    {
        $this->step = $step;

        return $this;
    }
}
