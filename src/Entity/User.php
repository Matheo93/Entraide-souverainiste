<?php

namespace App\Entity;

use App\Entity\Pages\Pages;
use App\Entity\Announces\Announces;
use App\Entity\Stats\Stats;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="Cet email existe déjà")
 */
class User implements UserInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=180, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(type="json")
	 */
	private $roles = [];

	/**
	 * @var string The hashed password
	 * @ORM\Column(type="string")
	 */
	private $password;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $isVerified = false;


	/**
	 * @ORM\OneToMany(targetEntity=Stats::class, mappedBy="user", orphanRemoval=true)
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="user", nullable=true, onDelete="SET NULL")
	 */
	private $stats;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $registered_at;

    /**
     * @ORM\OneToMany(targetEntity=Announces::class, mappedBy="user")
     */
    private $announces;


    /**
     * @ORM\OneToMany(targetEntity=Pages::class, mappedBy="author")
     */
    private $pages;

    /**
     * @ORM\Column(type="integer")
     */
    private $referedId;
	public function __construct()
         	{
         		$this->stats = new ArrayCollection();
         		$this->registered_at = new \Datetime();
         		$this->announces = new ArrayCollection();
         	}

	public function getId(): ?int
             {
             	return $this->id;
             }

	public function getEmail(): ?string
             {
             	return $this->email;
             }

	public function setEmail(string $email): self
             {
             	$this->email = $email;
                           
             	return $this;
             }

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername(): string
             {
             	return (string) $this->email;
             }

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
             {
             	$roles = $this->roles;
             	// guarantee every user at least has ROLE_USER
             	$roles[] = 'ROLE_USER';
                           
             	return array_unique($roles);
             }

	public function setRoles(array $roles): self
             {
             	$this->roles = $roles;
                           
             	return $this;
             }

	/**
	 * @see UserInterface
	 */
	public function getPassword(): string
             {
             	return (string) $this->password;
             }

	public function setPassword(string $password): self
             {
             	$this->password = $password;
                           
             	return $this;
             }

	/**
	 * @see UserInterface
	 */
	public function getSalt()
             {
             	// not needed when using the "bcrypt" algorithm in security.yaml
             }

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials()
             {
             	// If you store any temporary, sensitive data on the user, clear it here
             	// $this->plainPassword = null;
             }

	public function isVerified(): bool
             {
             	return $this->isVerified;
             }

	public function setIsVerified(bool $isVerified): self
             {
             	$this->isVerified = $isVerified;
                           
             	return $this;
             }

	/**
	 * @return Collection|Stats[]
	 */
	public function getStats(): Collection
             {
             	return $this->stats;
             }

	public function addStat(Stats $stat): self
             {
             	if (!$this->stats->contains($stat)) {
             		$this->stats[] = $stat;
             		$stat->setUser($this);
             	}
                           
             	return $this;
             }

	public function removeStat(Stats $stat): self
             {
             	if ($this->stats->removeElement($stat)) {
             		// set the owning side to null (unless already changed)
             		if ($stat->getUser() === $this) {
             			$stat->setUser(null);
             		}
             	}
                           
             	return $this;
             }

	public function getRegisteredAt(): ?\DateTimeInterface
             {
             	return $this->registered_at;
             }

	public function setRegisteredAt(\DateTimeInterface $registered_at): self
             {
             	$this->registered_at = $registered_at;
                           
             	return $this;
             }

    /**
     * @return Collection|Announces[]
     */
    public function getAnnounces(): Collection
    {
        return $this->announces;
    }

    public function addAnnounce(Announces $announce): self
    {
        if (!$this->announces->contains($announce)) {
            $this->announces[] = $announce;
            $announce->setUser($this);
        }

        return $this;
    }

    public function removeAnnounce(Announces $announce): self
    {
        if ($this->announces->removeElement($announce)) {
            // set the owning side to null (unless already changed)
            if ($announce->getUser() === $this) {
                $announce->setUser(null);
            }
        }

        return $this;
    }

    public function getReferedId(): ?int
    {
        return $this->referedId;
    }

    public function setReferedId(int $referedId): self
    {
        $this->referedId = $referedId;

        return $this;
    }

    /**
     * Get behavior stats (loaded separately from repository)
     * Used for Discord moderation display
     */
    public function getBehaviorStats(): ?\App\Entity\Behavior\UserBehaviorStats
    {
        // This will be loaded via repository when needed
        // Not a doctrine relationship to keep it invisible
        return null;
    }

    public function getFullName(): string
    {
        return $this->email; // Fallback to email since no firstname/lastname
    }

}
