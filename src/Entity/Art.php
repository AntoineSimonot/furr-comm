<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ArtRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ArtRepository::class)]
#[ApiResource(
    collectionOperations: [
        'get' => ['method' => 'get'],
        'post' => ['method' => 'post'],
    ],
    itemOperations: [
        'get' => ['method' => 'get'],
        'put' => [
            'normalization_context' => ['groups' => ['art:put']],
        ],
    ],
    denormalizationContext: ['groups' => ['art:write']],
    normalizationContext: ['groups' => ['art:read']],
)]
class Art
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["comment:read"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["art:read", "art:write", "art:put", "comment:read"])]
    private $title;

    #[ORM\Column(type: 'text')]
    #[Groups(["art:read", "art:write", "art:put"])]
    private $description;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'art')]
    #[Groups(["art:read", "art:write", "art:put"])]
    private $artist;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(iri: 'http://schema.org/image')]
    #[Groups(["art:read", "art:write", "art:put"])]
    public ?MediaObject $image = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'likes')]
    #[Groups(["art:read", "art:write", "art:put"])]
    private $likes;

    #[ORM\OneToMany(mappedBy: 'art', targetEntity: Comment::class)]
    #[Groups(["art:read", "art:write", "art:put"])]
    private $comments;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getArtist(): ?user
    {
        return $this->artist;
    }

    public function setArtist(?user $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return Collection<int, user>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(user $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
        }

        return $this;
    }

    public function removeLike(user $like): self
    {
        $this->likes->removeElement($like);

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
            $comment->setArt($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArt() === $this) {
                $comment->setArt(null);
            }
        }

        return $this;
    }
}
