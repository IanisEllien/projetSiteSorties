<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà utilisé par un autre compte")
 * @UniqueEntity("pseudo", message="Ce pseudo est déjà utilisé par un autre compte")
 */
class Participant implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(message="L'email {{ value }} n'est pas un mail valide")
     * @Assert\NotBlank(message="Veuillez renseigner votre email")
     * @Assert\Length(max=180,
     *     maxMessage="L'email ne peut pas faire plus de {{ limit }} caractères")
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
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(min=2, max=50,
     *     minMessage="Le pseudo doit faire au moins {{ limit }} caractères",
     *     maxMessage="Le pseudo ne peut pas faire plus de {{ limit }} caractères")
     */
    private $pseudo;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre nom")
     * @Assert\Length(min=2, max=50,
     *     minMessage="Le nom doit faire au moins {{ limit }} caractères",
     *     maxMessage="Le nom ne peut pas faire plus de {{ limit }} caractères")
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $nom;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre prenom")
     * @Assert\Length(min=2, max=50,
     *     minMessage="Le prénom doit faire au moins {{ limit }} caractères",
     *     maxMessage="Le prénom ne peut pas faire plus de {{ limit }} caractères")
     * @ORM\Column(type="string", length=50)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Veuillez renseigner votre numéro de téléphone")
     *@Assert\Regex(
     *     pattern="/^(0|\+33)[1-9]([-. ]?[0-9]{2}){4}$/",
     *     message="Veuillez indiquer un numéro de téléphone valide")
     */

    private $telephone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\ManyToMany(targetEntity=Sortie::class, inversedBy="participants")
     */
    private $sorties;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="Organisateur")
     */
    private $sorties_organisateur;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sorties_organisateur = new ArrayCollection();
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
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        $this->sorties->removeElement($sorty);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesOrganisateur(): Collection
    {
        return $this->sorties_organisateur;
    }

    public function addSortiesOrganisateur(Sortie $sortiesOrganisateur): self
    {
        if (!$this->sorties_organisateur->contains($sortiesOrganisateur)) {
            $this->sorties_organisateur[] = $sortiesOrganisateur;
            $sortiesOrganisateur->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortiesOrganisateur(Sortie $sortiesOrganisateur): self
    {
        if ($this->sorties_organisateur->removeElement($sortiesOrganisateur)) {
            // set the owning side to null (unless already changed)
            if ($sortiesOrganisateur->getOrganisateur() === $this) {
                $sortiesOrganisateur->setOrganisateur(null);
            }
        }

        return $this;
    }
}
