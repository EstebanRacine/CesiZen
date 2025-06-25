<?php

namespace App\Entity;

use App\Repository\InfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InfoRepository::class)]
class Info
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['info:read', 'menu:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['info:read', 'menu:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['info:read', 'menu:read'])]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['info:read', 'menu:read'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['info:read', 'menu:read'])]
    private ?bool $actif = null;

    #[ORM\Column]
    #[Groups(['info:read', 'menu:read'])]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info:read'])]
    private ?\DateTime $dateModification = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['info:read'])]
    private ?\DateTime $dateSuppression = null;

    #[ORM\ManyToOne(inversedBy: 'infos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['info:read'])]
    private ?User $createur = null;

    #[ORM\ManyToOne]
    #[Groups(['info:read'])]
    private ?User $modificateur = null;

    #[ORM\ManyToOne]
    #[Groups(['info:read'])]
    private ?User $supprimeur = null;

    #[ORM\ManyToOne(inversedBy: 'infos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['info:read'])]
    private ?Menu $menu = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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

    public function getDateModification(): ?\DateTime
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTime $dateModification): static
    {
        $this->dateModification = $dateModification;

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

    public function getCreateur(): ?User
    {
        return $this->createur;
    }

    public function setCreateur(?User $createur): static
    {
        $this->createur = $createur;

        return $this;
    }

    public function getModificateur(): ?User
    {
        return $this->modificateur;
    }

    public function setModificateur(?User $modificateur): static
    {
        $this->modificateur = $modificateur;

        return $this;
    }

    public function getSupprimeur(): ?User
    {
        return $this->supprimeur;
    }

    public function setSupprimeur(?User $supprimeur): static
    {
        $this->supprimeur = $supprimeur;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }
}
