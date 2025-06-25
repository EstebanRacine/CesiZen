<?php

namespace App\Entity;

use App\Repository\EmotionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EmotionRepository::class)]
class Emotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['emotion:read', 'categorie_emotion:read', 'tracker:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['emotion:read', 'categorie_emotion:read', 'tracker:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['emotion:read', 'categorie_emotion:read', 'tracker:read'])]
    private ?string $icone = null;

    #[ORM\Column]
    #[Groups(['emotion:read'])]
    private ?bool $actif = null;

    #[ORM\Column]
    #[Groups(['emotion:read'])]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['emotion:read'])]
    private ?\DateTime $dateSuppression = null;

    #[ORM\ManyToOne]
    #[Groups(['emotion:read'])]
    private ?User $dernierModificateur = null;

    #[ORM\ManyToOne(inversedBy: 'emotions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['emotion:read', 'tracker:read'])]
    private ?CategorieEmotion $categorie = null;

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

    public function getCategorie(): ?CategorieEmotion
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieEmotion $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
}
