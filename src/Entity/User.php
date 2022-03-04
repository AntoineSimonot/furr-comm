<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    collectionOperations: [
        'get' => ['method' => 'get'],
        'post' => [
            'method' => 'post',
            'normalization_context' => ['groups' => ['user:post']],
        ],
    ],
    itemOperations: [
        'get' => ['method' => 'get'],
        'put' => [
            'normalization_context' => ['groups' => ['user:put']],
        ],
        'delete' => ['method' => 'delete'],
    ],
    denormalizationContext: ["groups" => ["user", "user:write"]],
    normalizationContext: ["groups" => ["user", "user:read"]]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["comment:read"])]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(["user:read", "user:write", "user:put", "art:read"])]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    #[Groups(["user:write"])]
    #[Assert\NotBlank]
    private $password;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(iri: 'http://schema.org/image')]
    #[Groups(["user:read", "user:write", "user:put"])]
    public ?MediaObject $image = null;

    #[ORM\ManyToMany(targetEntity: self::class, inversedBy: 'followers')]
    #[Groups(["user:read", "user:write", "user:put"])]
    private $followed;

    #[ORM\ManyToMany(targetEntity: self::class, mappedBy: 'followed')]
    #[Groups(["user:read", "user:write", "user:put"])]
    private $followers;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Art::class)]
    #[Groups(["user:read", "user:write", "user:put"])]
    #[ORM\JoinColumn(onDelete: "cascade")]
    #[ORM\JoinColumn(nullable: true)]
    private $art;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["user:read", "user:write", "user:put", "art:read","comment:read"])]
    #[Assert\NotBlank]
    private $pseudo;

    #[ORM\ManyToMany(targetEntity: Art::class, mappedBy: 'likes')]
    #[Groups(["user:read", "user:write", "user:put"])]
    private $likes;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class)]
    #[Groups(["user:read", "user:write", "user:put"])]
    private $comments;

    #[ORM\OneToMany(mappedBy: 'artist', targetEntity: Commission::class)]
    #[Groups(["user:read", "user:write", "user:put"])]
    private $artist_commissions;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Commission::class)]
    #[Groups(["user:read", "user:write", "user:put"])]
    private $client_commissions;

    public function __construct()
    {
        $this->art = new ArrayCollection();
        $this->followed = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->artist_commissions = new ArrayCollection();
        $this->client_commissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getPseudo() : ?string
    {
        return $this->pseudo;
    }

    /**
     * @param mixed $pseudo
     */
    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;

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

    /**
     * @return Collection<int, Art>
     */
    public function getArt(): Collection
    {
        return $this->art;
    }

    public function addArt(Art $art): self
    {
        if (!$this->art->contains($art)) {
            $this->art[] = $art;
            $art->setArtist($this);
        }

        return $this;
    }

    public function removeArt(Art $art): self
    {
        if ($this->art->removeElement($art)) {
            // set the owning side to null (unless already changed)
            if ($art->getArtist() === $this) {
                $art->setArtist(null);
            }
        }

        return $this;
    }

    /**
     * @param ArrayCollection $art
     */
    public function setArt(ArrayCollection $art): void
    {
        $this->art = $art;
    }

    /**
     * @return Collection|self[]
     */
    public function getFollowed(): Collection
    {
        return $this->followed;
    }

    public function addFollowed(self $followed): self
    {
        if (!$this->followed->contains($followed)) {
            $this->followed[] = $followed;
        }

        return $this;
    }

    public function removeFollowed(self $followed): self
    {
        $this->followed->removeElement($followed);

        return $this;
    }
    /**
     * @return Collection|self[]
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollowers(self $followers): self
    {
        if (!$this->followers->contains($followers)) {
            $this->followers[] = $followers;
            $followers->addFollowed($this);
        }

        return $this;
    }

    public function removeFollowers(self $followers): self
    {
        if ($this->followers->removeElement($followers)) {
            $followers->removeFollowed($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Art>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Art $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->addLike($this);
        }

        return $this;
    }

    public function removeLike(Art $like): self
    {
        if ($this->likes->removeElement($like)) {
            $like->removeLike($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commission>
     */
    public function getArtistCommissions(): Collection
    {
        return $this->artist_commissions;
    }

    public function addArtistCommissions(Commission $artist_commissions): self
    {
        if (!$this->artist_commissions->contains($artist_commissions)) {
            $this->artist_commissions[] = $artist_commissions;
            $artist_commissions->setArtist($this);
        }

        return $this;
    }

    public function removeArtistCommissions(Commission $artist_commissions): self
    {
        if ($this->artist_commissions->removeElement($artist_commissions)) {
            // set the owning side to null (unless already changed)
            if ($artist_commissions->getArtist() === $this) {
                $artist_commissions->setArtist(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commission>
     */
    public function getClientCommissions(): Collection
    {
        return $this->client_commissions;
    }

    public function addClientCommission(Commission $clientCommission): self
    {
        if (!$this->client_commissions->contains($clientCommission)) {
            $this->client_commissions[] = $clientCommission;
            $clientCommission->setClient($this);
        }

        return $this;
    }

    public function removeClientCommission(Commission $clientCommission): self
    {
        if ($this->client_commissions->removeElement($clientCommission)) {
            // set the owning side to null (unless already changed)
            if ($clientCommission->getClient() === $this) {
                $clientCommission->setClient(null);
            }
        }

        return $this;
    }
}
