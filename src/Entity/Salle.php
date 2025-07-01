<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numbre = null;

    #[ORM\Column]
    private ?int $capacity = null;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'salle')]
    private Collection $seances;

    #[ORM\OneToMany(mappedBy: 'salle', targetEntity: Seat::class, orphanRemoval: true)]
    private Collection $seats;


    public function __construct()
    {
        $this->seances = new ArrayCollection();
        $this->seats = new ArrayCollection();

    }

    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return 'Salle ' . $this->numbre;
    }

    public function getNumbre(): ?string
    {
        return $this->numbre;
    }

    public function setNumbre(string $numbre): static
    {
        $this->numbre = $numbre;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setSalle($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            // set the owning side to null (unless already changed)
            if ($seance->getSalle() === $this) {
                $seance->setSalle(null);
            }
        }

        return $this;
    }
}

