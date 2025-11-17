<?php

namespace App\Entity\Settings;

use App\Entity\SEO\SEOEntities;

use App\Repository\Settings\CitiesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CitiesRepository::class)
 */
class Cities
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=25, nullable=true)
	 */
	private $code_insee;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $name;

	/**
	 * @ORM\Column(type="integer", length=11, nullable=true)
	 */
	private $code;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $libelle;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $coords;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $slug;


	public function getId(): ?int {
		return $this->id;
	}

	public function getCodeInsee(): ?string {
		return $this->code_insee;
	}

	public function setCodeInsee(string $code_insee): self {
		$this->code_insee = $code_insee;

		return $this;
	}
	public function getName(): ?string {
		return $this->name;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}
	public function getCode(): ?string {
		return $this->code;
	}

	public function setCode($code): self {
		$this->code = $code;

		return $this;
	}

	public function getLibelle(): ?string {
		return $this->libelle;
	}

	public function setLibelle($libelle): self {
		$this->libelle = $libelle;

		return $this;
	}

	public function getCoords(): ?string {
		return $this->coords;
	}

	public function setCoords($coords): self {
		$this->coords = $coords;

		return $this;
	}

	public function getLigne5(): ?string {
		return $this->Ligne_5;
	}

	public function setLigne5($Ligne_5): self {
		$this->Ligne_5 = $Ligne_5;

		return $this;
	}

	public function getSlug(): ?string {
		return $this->slug;
	}

	public function setSlug($slug): self {
		$this->slug = $slug;

		return $this;
	}
}
