<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "La sortie doit avoir un titre")]
    #[Assert\Length(max: 30 ,maxMessage: "Le titre ne doit pas dépasser les 30 caractères" )]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La sortie doit avoir une date de début")]
    #[Assert\GreaterThanOrEqual('today',message: "la date de début ne peut pas être programmé dans le passé")]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La sortie doit avoir une date de fin")]
    #[Assert\GreaterThanOrEqual(propertyPath : 'dateDebut', message: "la date de fin doit être programmé après la date de début")]
    private ?\DateTime $dateCloture = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La sortie doit avoir un nombre d'inscription maximum")]
    #[Assert\Positive(message: "Le nombre d'inscription maximum doit être positif")]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La sortie doit avoir une description")]
    #[Assert\Length(max: 255,maxMessage: "La description peut contenir 255 caractères au maximum")]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image]
    private ?string $urlPhoto = null;

    #[ORM\OneToMany(
        targetEntity: Inscription::class,
        mappedBy: 'sortie',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $inscriptions;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Utilisateur $organisateur = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "La sortie doit avoir un lieu")]
    private ?Lieu $lieu = null;

    #[ORM\ManyToOne(inversedBy: 'sorties')]
    private ?Categorie $categorie = null;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // Tu peux supprimer setId(), normalement on ne modifie pas un id généré automatiquement.

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateCloture(): ?\DateTime
    {
        return $this->dateCloture;
    }

    public function setDateCloture(\DateTime $dateCloture): static
    {
        $this->dateCloture = $dateCloture;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->etat;
    }

    public function setEtat(bool $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): static
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }


    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setSortie($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
       $this->inscriptions->removeElement($inscription);

        return $this;
    }

    public function getOrganisateur(): ?Utilisateur
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Utilisateur $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motifAnnulation = null;

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): static
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }
}
