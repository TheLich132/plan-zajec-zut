<?php

namespace App\Entity;

use App\Enum\Degree;
use App\Repository\SubjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'subject')]
    private Collection $lessons;

    #[ORM\Column(nullable: true, enumType: Degree::class)]
    private ?Degree $degree = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStationary = null;

    #[ORM\ManyToOne(inversedBy: 'subjects')]
    private ?Major $major = null;

    #[ORM\ManyToOne(inversedBy: 'subjects')]
    private ?Faculty $faculty = null;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setSubject($this);
        }

        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            // set the owning side to null (unless already changed)
            if ($lesson->getSubject() === $this) {
                $lesson->setSubject(null);
            }
        }

        return $this;
    }

    public function getDegree(): ?Degree
    {
        return $this->degree;
    }

    public function setDegree(?Degree $degree): static
    {
        $this->degree = $degree;

        return $this;
    }

    public function isStationary(): ?bool
    {
        return $this->isStationary;
    }

    public function setStationary(?bool $isStationary): static
    {
        $this->isStationary = $isStationary;

        return $this;
    }

    public function getMajor(): ?Major
    {
        return $this->major;
    }

    public function setMajor(?Major $major): static
    {
        $this->major = $major;

        return $this;
    }

    public function getFaculty(): ?Faculty
    {
        return $this->faculty;
    }

    public function setFaculty(?Faculty $faculty): static
    {
        $this->faculty = $faculty;

        return $this;
    }
}
