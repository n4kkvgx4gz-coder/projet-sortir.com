<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date_inscription = null;

    #[ORM\Column]
    private ?int $id_sortie = null;

    #[ORM\Column]
    private ?int $id_participant = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sortie $sortie = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateInscription(): ?\DateTime
    {
        return $this->date_inscription;
    }

    public function setDateInscription(\DateTime $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

    public function getIdSortie(): ?int
    {
        return $this->id_sortie;
    }

    public function setIdSortie(int $id_sortie): static
    {
        $this->id_sortie = $id_sortie;

        return $this;
    }

    public function getIdParticipant(): ?int
    {
        return $this->id_participant;
    }

    public function setIdParticipant(int $id_participant): static
    {
        $this->id_participant = $id_participant;

        return $this;
    }

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(?Sortie $sortie): static
    {
        $this->sortie = $sortie;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }
}
