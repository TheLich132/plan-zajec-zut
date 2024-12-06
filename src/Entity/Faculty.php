<?php

namespace App\Entity;

use App\Repository\FacultyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacultyRepository::class)]
class Faculty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\OneToMany(targetEntity: Room::class, mappedBy: 'faculty')]
    private Collection $rooms;

    /**
     * @var Collection<int, Major>
     */
    #[ORM\OneToMany(targetEntity: Major::class, mappedBy: 'faculty')]
    private Collection $majors;

    /**
     * @var Collection<int, Subject>
     */
    #[ORM\OneToMany(targetEntity: Subject::class, mappedBy: 'faculty')]
    private Collection $subjects;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->majors = new ArrayCollection();
        $this->subjects = new ArrayCollection();
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
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setFaculty($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getFaculty() === $this) {
                $room->setFaculty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Major>
     */
    public function getMajors(): Collection
    {
        return $this->majors;
    }

    public function addMajor(Major $major): static
    {
        if (!$this->majors->contains($major)) {
            $this->majors->add($major);
            $major->setFaculty($this);
        }

        return $this;
    }

    public function removeMajor(Major $major): static
    {
        if ($this->majors->removeElement($major)) {
            // set the owning side to null (unless already changed)
            if ($major->getFaculty() === $this) {
                $major->setFaculty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subject>
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): static
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
            $subject->setFaculty($this);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): static
    {
        if ($this->subjects->removeElement($subject)) {
            // set the owning side to null (unless already changed)
            if ($subject->getFaculty() === $this) {
                $subject->setFaculty(null);
            }
        }

        return $this;
    }
}
