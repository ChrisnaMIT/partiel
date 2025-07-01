<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;


    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'film')]
    private Collection $seances;


    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'film', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $images;



    public function __construct()
    {
        $this->seances = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }





    public function addImage(Image $image): self {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setFilm($this);
        }
        return $this;
    }

    public function removeImage(Image $image): self {
        if ($this->images->removeElement($image)) {
            if ($image->getFilm() === $this) {
                $image->setFilm(null);
            }
        }
        return $this;
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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

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
            $seance->setFilm($this);
        }

        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            // set the owning side to null (unless already changed)
            if ($seance->getFilm() === $this) {
                $seance->setFilm(null);
            }
        }

        return $this;
    }
}
