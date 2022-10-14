<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $visibility = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 2550, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $disclaimer = null;

    #[ORM\Column]
    private ?int $author = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated = null;

    #[ORM\Column(nullable: true)]
    private ?int $shop_qty = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $shop_last = null;

    public function getAll(): array
    {
        $array = [
            'id'                => $this->getId(),
            'status'            => $this->getStatus(),
            'visibility'        => $this->getVisibility(),
            'name'              => ucfirst($this->getName()),
            'description'       => $this->getDescription(),
            'disclaimer'        => $this->getDisclaimer(),
            'author'            => $this->getAuthor(),
            'created'           => $this->getCreated()->format('Y-m-d H:i:s'),
            'updated'           => $this->getUpdated()->format('Y-m-d H:i:s')
        ];

        return $array;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

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

    public function getDisclaimer(): ?string
    {
        return $this->disclaimer;
    }

    public function setDisclaimer(?string $disclaimer): self
    {
        $this->disclaimer = $disclaimer;

        return $this;
    }

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function setAuthor(int $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(?\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getShopQty(): ?int
    {
        return $this->shop_qty;
    }

    public function setShopQty(?int $shop_qty): self
    {
        $this->shop_qty = $shop_qty;

        return $this;
    }

    public function getShopLast(): ?\DateTimeInterface
    {
        return $this->shop_last;
    }

    public function setShopLast(?\DateTimeInterface $shop_last): self
    {
        $this->shop_last = $shop_last;

        return $this;
    }
}
