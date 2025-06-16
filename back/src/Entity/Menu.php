<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $icone = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\Column]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateSuppression = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $dernierModificateur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(string $icone): static
    {
        $this->icone = $icone;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateSuppression(): ?\DateTime
    {
        return $this->dateSuppression;
    }

    public function setDateSuppression(?\DateTime $dateSuppression): static
    {
        $this->dateSuppression = $dateSuppression;

        return $this;
    }

    public function getDernierModificateur(): ?User
    {
        return $this->dernierModificateur;
    }

    public function setDernierModificateur(?User $dernierModificateur): static
    {
        $this->dernierModificateur = $dernierModificateur;

        return $this;
    }
}
