<?php

namespace App\Entity\Announces;

use App\Repository\Announces\AnnouncesMetasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnnouncesMetasRepository::class)
 */
class AnnouncesMetas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Announces::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $announce;

    /**
     * @ORM\Column(type="json")
     */
    private $metas = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnounce(): ?Announces
    {
        return $this->announce;
    }

    public function setAnnounce(Announces $announce): self
    {
        $this->announce = $announce;

        return $this;
    }

    public function getMetas(): ?array
    {
        return $this->metas;
    }

    public function setMetas(array $metas): self
    {
        $this->metas = $metas;

        return $this;
    }
}
