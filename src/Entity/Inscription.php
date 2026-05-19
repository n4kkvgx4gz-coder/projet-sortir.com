<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
#[ORM\Table(name: 'inscription')]
class Inscription
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(name: 'id_sortie', referencedColumnName: 'id_sortie', nullable: false)]
    private ?Sortie $sortie = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(name: 'id_participant', referencedColumnName: 'id_utilisateur', nullable: false)]
    private ?User $participant = null;

    #[ORM\Column(name: 'date_inscription')]
    private ?\DateTime $dateInscription = null;

    public function getSortie(): ?Sortie
    {
        return $this->sortie;
    }

    public function setSortie(?Sortie $sortie): static
    {
        $this->sortie = $sortie;
        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): static
    {
        $this->participant = $participant;
        return $this;
    }

    public function getDateInscription(): ?\DateTime
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTime $dateInscription): static
    {
        $this->dateInscription = $dateInscription;
        return $this;
    }
}
