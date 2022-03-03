<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ApiResource(
    collectionOperations: [
        'get' => ['method' => 'get'],
        'post' => ['method' => 'post'],
    ],
    itemOperations: [
        'get' => ['method' => 'get'],
        'put' => [
            'normalization_context' => ['groups' => ['comment:put']],
        ],
    ],
    denormalizationContext: ['groups' => ['comment:write']],
    normalizationContext: ['groups' => ['comment:read']],
)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    #[Groups(["comment:read", "comment:write", "comment:put"])]
    private $content;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[Groups(["comment:read", "comment:write", "comment:put"])]
    private $user;

    #[ORM\ManyToOne(targetEntity: Art::class, inversedBy: 'comments')]
    #[Groups(["comment:read", "comment:write", "comment:put"])]
    private $art;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getArt(): ?Art
    {
        return $this->art;
    }

    public function setArt(?Art $art): self
    {
        $this->art = $art;

        return $this;
    }
}
