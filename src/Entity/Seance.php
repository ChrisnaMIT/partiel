<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;


    #[ORM\Column]
    private ?\DateTime $startTime = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Film $film = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $salle = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'seance')]
    private Collection $reservations;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'seance', targetEntity: Seat::class, cascade: ['persist', 'remove'])]
    private Collection $seats;



    #[ORM\Column]
    private ?int $placeAvailable = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->categories = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }



    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(?Film $film): static
    {
        $this->film = $film;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setSeance($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getSeance() === $this) {
                $reservation->setSeance(null);
            }
        }

        return $this;
    }



    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }


    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setSeance($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getSeance() === $this) {
                $category->setSeance(null);
            }
        }

        return $this;
    }

    public function getPlaceAvailable(): ?int
    {
        return $this->placeAvailable;
    }

    public function setPlaceAvailable(int $placeAvailable): static
    {
        $this->placeAvailable = $placeAvailable;

        return $this;
    }


    public function decreaseAvailablePlaces(int $quantity): void
    {
        if ($this->placeAvailable !== null && $this->placeAvailable >= $quantity) {
            $this->placeAvailable -= $quantity;
        } else {
            throw new \LogicException('Nombre de places insuffisant pour la réservation.');
        }
    }



}
