<?php

namespace App\Entity;

use App\Repository\StepsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepsRepository::class)]
class Steps
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    // #[ORM\Column(nullable: true)]
    // private array $extras = [];

    // #[ORM\Column(length: 2550, nullable: true)]
    // private ?string $recommendations = null;

    #[ORM\Column]
    private ?int $recipeId = null;

    #[ORM\Column]
    private ?int $step = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    // public function getExtras(): array
    // {
    //     return $this->extras;
    // }

    // public function setExtras(?array $extras): self
    // {
    //     $this->extras = $extras;

    //     return $this;
    // }

    // public function getRecommendations(): ?string
    // {
    //     return $this->recommendations;
    // }

    // public function setRecommendations(?string $recommendations): self
    // {
    //     $this->recommendations = $recommendations;

    //     return $this;
    // }

    public function getRecipeId(): ?int
    {
        return $this->recipeId;
    }

    public function setRecipeId(int $recipeId): self
    {
        $this->recipeId = $recipeId;

        return $this;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(int $step): self
    {
        $this->step = $step;

        return $this;
    }
}
