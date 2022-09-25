<?php

namespace App\Entity;

use App\Repository\IngredientUnitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientUnitRepository::class)]
class IngredientUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $unit_name = null;

    #[ORM\Column(length: 10)]
    private ?string $unit_abbreviation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUnitName(): ?string
    {
        return $this->unit_name;
    }

    public function setUnitName(string $unit_name): self
    {
        $this->unit_name = $unit_name;

        return $this;
    }

    public function getUnitAbbreviation(): ?string
    {
        return $this->unit_abbreviation;
    }

    public function setUnitAbbreviation(string $unit_abbreviation): self
    {
        $this->unit_abbreviation = $unit_abbreviation;

        return $this;
    }
}
