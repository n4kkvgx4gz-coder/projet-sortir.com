<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "le nom de la ville est obligatoire")]
    #[Assert\Length(max: 30,maxMessage: "Le nom de la ville ne peut pas faire plus de 30 caratères")]
    private ?string $nom_ville = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message:"le code postal de la ville est obligatoire")]
    #[Assert\Length(max: 10,maxMessage: "Le code postal ne peut pas faire plus de 10 caratères")]
    private ?string $code_postal = null;

    /**
     * @var Collection<int, Lieu>
     */
    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: 'ville')]
    private Collection $lieux;

    /**
     * @var Collection<int, Campus>
     */
    #[ORM\OneToMany(targetEntity: Campus::class, mappedBy: 'ville')]
    private Collection $campuses;

    public function __construct()
    {
        $this->lieux = new ArrayCollection();
        $this->campuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNomVille(): ?string
    {
        return $this->nom_ville;
    }

    public function setNomVille(string $nom_ville): static
    {
        $this->nom_ville = $nom_ville;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): static
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    public function addLieux(Lieu $lieux): static
    {
        if (!$this->lieux->contains($lieux)) {
            $this->lieux->add($lieux);
            $lieux->setVille($this);
        }

        return $this;
    }

    public function removeLieux(Lieu $lieux): static
    {
        if ($this->lieux->removeElement($lieux)) {
            // set the owning side to null (unless already changed)
            if ($lieux->getVille() === $this) {
                $lieux->setVille(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Campus>
     */
    public function getCampuses(): Collection
    {
        return $this->campuses;
    }

    public function addCampus(Campus $campus): static
    {
        if (!$this->campuses->contains($campus)) {
            $this->campuses->add($campus);
            $campus->setVille($this);
        }

        return $this;
    }

    public function removeCampus(Campus $campus): static
    {
        if ($this->campuses->removeElement($campus)) {
            // set the owning side to null (unless already changed)
            if ($campus->getVille() === $this) {
                $campus->setVille(null);
            }
        }

        return $this;
    }
}
