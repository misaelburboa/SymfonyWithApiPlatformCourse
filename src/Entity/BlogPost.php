<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\AuthoredEntityInterface;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlogPostRepository")
 * @ApiResource(
 *      denormalizationContext={
 *          "groups"={"post"}
 *      },
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor() == user"
 *          }
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          }
 *      }
 * )
 */
class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min=10
     * )
     * @Groups({"post"})
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $published;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min=20,
     *      max=3000
     * )
     * @Groups({"post"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @Groups({"post"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="blogPost")
     * @ApiSubresource()
     * @var $comments
     */
    private $comments;

    public function __construct()
    {
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

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(DateTimeInterface $published): PublishedDateEntityInterface
    {
        $this->published = $published;

        return $this;
    }

    public function getContent():?string
    {
        return $this->content;
    }

    public function setContent(string $content):self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param UserInterface $author
     * @return AuthoredEntityInterface
     */
    public function setAuthor(UserInterface $author):AuthoredEntityInterface
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthor ():User
    {
        return $this->author;
    }

    public function getSlug():?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug):self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getComments():Collection
    {
        return $this->comments;
    }
}
