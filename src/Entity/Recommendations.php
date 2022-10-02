<?php

namespace App\Entity;

use App\Repository\RecommendationsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecommendationsRepository::class)]
class Recommendations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rec_text = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $typeId = null;

    // #[ORM\Column(nullable: true)]
    // private array $extras = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecText(): ?string
    {
        return $this->rec_text;
    }

    public function setRecText(string $rec_text): self
    {
        $this->rec_text = $rec_text;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setTypeId(int $typeId): self
    {
        $this->typeId = $typeId;

        return $this;
    }
}
