<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $seats = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seance $seance = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reservationUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSeats(array $seats): static
    {
        $this->seats = implode(',', $seats);
        return $this;
    }

    public function getSeats(): array
    {
        return explode(',', $this->seats ?? '');
    }



    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): static
    {
        $this->seance = $seance;

        return $this;
    }

    public function getReservationUser(): ?User
    {
        return $this->reservationUser;
    }

    public function setReservationUser(?User $reservationUser): static
    {
        $this->reservationUser = $reservationUser;

        return $this;
    }
}
