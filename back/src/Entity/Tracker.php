<?php

namespace App\Entity;

use App\Repository\TrackerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TrackerRepository::class)]
class Tracker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tracker:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['tracker:read'])]
    private ?\DateTime $datetime = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['tracker:read'])]
    private ?string $commentaire = null;

    #[ORM\Column]
    #[Groups(['tracker:read'])]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'trackers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tracker:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tracker:read'])]
    private ?Emotion $emotion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTime $datetime): static
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEmotion(): ?Emotion
    {
        return $this->emotion;
    }

    public function setEmotion(?Emotion $emotion): static
    {
        $this->emotion = $emotion;

        return $this;
    }
}
