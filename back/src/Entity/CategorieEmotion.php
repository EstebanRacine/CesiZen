<?php

namespace App\Entity;

use App\Repository\CategorieEmotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CategorieEmotionRepository::class)]
class CategorieEmotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categorie_emotion:read', 'emotion:read', 'tracker:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['categorie_emotion:read', 'emotion:read', 'tracker:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 15)]
    #[Groups(['categorie_emotion:read', 'emotion:read', 'tracker:read'])]
    private ?string $couleur = null;

    /**
     * @var Collection<int, Emotion>
     */
    #[ORM\OneToMany(targetEntity: Emotion::class, mappedBy: 'categorie')]
    #[Groups(['categorie_emotion:read'])]
    private Collection $emotions;

    public function __construct()
    {
        $this->emotions = new ArrayCollection();
    }

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

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * @return Collection<int, Emotion>
     */
    public function getEmotions(): Collection
    {
        return $this->emotions;
    }

    public function addEmotion(Emotion $emotion): static
    {
        if (!$this->emotions->contains($emotion)) {
            $this->emotions->add($emotion);
            $emotion->setCategorie($this);
        }

        return $this;
    }

    public function removeEmotion(Emotion $emotion): static
    {
        if ($this->emotions->removeElement($emotion)) {
            // set the owning side to null (unless already changed)
            if ($emotion->getCategorie() === $this) {
                $emotion->setCategorie(null);
            }
        }

        return $this;
    }
}
