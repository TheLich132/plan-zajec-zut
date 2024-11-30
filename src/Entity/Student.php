<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $IndexNumber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIndexNumber(): ?int
    {
        return $this->IndexNumber;
    }

    public function setIndexNumber(int $IndexNumber): static
    {
        $this->IndexNumber = $IndexNumber;

        return $this;
    }
}
