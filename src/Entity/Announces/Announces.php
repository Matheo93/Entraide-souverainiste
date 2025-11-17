<?php

namespace App\Entity\Announces;

use App\Entity\Announces\AnnouncesMetas;
use App\Entity\Categories;
use App\Entity\User;
use App\Repository\AnnouncesRepository;
use App\Repository\Announces\AnnouncesMetasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnnouncesRepository::class)
 */
class Announces
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $localisation;

    /**
     * @ORM\Column(type="integer")
     */
    private $distance;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRemote;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdded;

    /**
     * @ORM\ManyToMany(targetEntity=Categories::class, inversedBy="announces")
     */
    private $categories;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="announces")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=AnnouncesRequests::class, mappedBy="announce")
     */
    private $announcesRequests;

    /**
     * @ORM\OneToOne(targetEntity=AnnouncesMetas::class, mappedBy="announce")
     */
    private $announcesMetas;


    public function __construct(
    ) {
        $date = new \DateTime();
        $this->setDateAdded($date);
        $this->categories = new ArrayCollection();

        $this->setIsActive(false);

        $this->setDistance(50);
        $this->announcesRequests = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getIsRemote(): ?bool
    {
        return $this->isRemote;
    }

    public function setIsRemote(bool $isRemote): self
    {
        $this->isRemote = $isRemote;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * @return Collection|Categories[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categories $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Categories $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|AnnouncesRequests[]
     */
    public function getAnnouncesRequests(): Collection
    {
        return $this->announcesRequests;
    }

    public function addAnnouncesRequest(AnnouncesRequests $announcesRequest): self
    {
        if (!$this->announcesRequests->contains($announcesRequest)) {
            $this->announcesRequests[] = $announcesRequest;
            $announcesRequest->setAnnounce($this);
        }

        return $this;
    }

    public function removeAnnouncesRequest(AnnouncesRequests $announcesRequest): self
    {
        if ($this->announcesRequests->removeElement($announcesRequest)) {
            // set the owning side to null (unless already changed)
            if ($announcesRequest->getAnnounce() === $this) {
                $announcesRequest->setAnnounce(null);
            }
        }

        return $this;
    }



    public function getAnnouncesMetas(){
        return $this->announcesMetas;
    }
}
