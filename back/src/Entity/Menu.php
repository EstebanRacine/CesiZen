<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['menu:read', 'info:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['menu:read', 'info:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['menu:read', 'info:read'])]
    private ?string $icone = null;

    #[ORM\Column]
    #[Groups(['menu:read'])]
    private ?bool $actif = null;

    #[ORM\Column]
    #[Groups(['menu:read'])]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['menu:read'])]
    private ?\DateTime $dateSuppression = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['menu:read'])]
    private ?User $dernierModificateur = null;

    /**
     * @var Collection<int, Info>
     */
    #[ORM\OneToMany(targetEntity: Info::class, mappedBy: 'menu')]
    #[Groups(['menu:read'])]
    private Collection $infos;

    public function __construct()
    {
        $this->infos = new ArrayCollection();
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

    /**
     * @return Collection<int, Info>
     */
    public function getInfos(): Collection
    {
        return $this->infos;
    }

    public function addInfo(Info $info): static
    {
        if (!$this->infos->contains($info)) {
            $this->infos->add($info);
            $info->setMenu($this);
        }

        return $this;
    }

    public function removeInfo(Info $info): static
    {
        if ($this->infos->removeElement($info)) {
            // set the owning side to null (unless already changed)
            if ($info->getMenu() === $this) {
                $info->setMenu(null);
            }
        }

        return $this;
    }

    #[Groups(['menu:read'])]
    public function getInfosActives(): Collection
    {
        return $this->infos->filter(fn(Info $info) => $info->isActif());
    }
}
