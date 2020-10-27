<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read:project"}},
 *     collectionOperations={},
 *     itemOperations={"GET"},
 * )
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 * @UniqueEntity("name", message="Le projet {{ value }} existe déjà.")
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"read:project"})
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @Groups({"read:project"})
     * @ORM\Column(type="string", length=100)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=ProjectImage::class, mappedBy="project", orphanRemoval=true)
     */
    private $projectImages;

    /**
     * @Groups({"read:project"})
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="projects")
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity=ProjectCategory::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    public function __construct()
    {
        $this->projectImages = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|ProjectImage[]
     */
    public function getProjectImages(): Collection
    {
        return $this->projectImages;
    }

    /**
     * @Groups({"read:project"})
     * @SerializedName("image")
     */
    public function getPrincipalProjectImage(): ?ProjectImage
    {
        $principalImage = $this->getProjectImages()
            ->filter(fn (ProjectImage $image) => $image->getIsCover() === true)
            ->first();

        return $principalImage === false ? null : $principalImage;
    }

    public function addProjectImage(ProjectImage $projectImage): self
    {
        if (!$this->projectImages->contains($projectImage)) {
            $this->projectImages[] = $projectImage;
            $projectImage->setProject($this);
        }

        return $this;
    }

    public function removeProjectImage(ProjectImage $projectImage): self
    {
        // set the owning side to null (unless already changed)
        if ($this->projectImages->removeElement($projectImage) && $projectImage->getProject() === $this) {
            $projectImage->setProject(null);
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getCategory(): ?ProjectCategory
    {
        return $this->category;
    }

    public function setCategory(?ProjectCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }
}
