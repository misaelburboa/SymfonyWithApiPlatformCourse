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
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BlogPostRepository")
 * @ApiResource(
 *      attributes={
 *          "order"={"published": "DESC"},
 *          "maximum_items_per_page"=10,
 *          "pagination_partial"=true
 *      },
 *      denormalizationContext={
 *          "groups"={"post"}
 *      },
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                 "groups"={"get-blog-post-with-author"}
 *             }
 *          },
 *          "put"={
 *              "access_control"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() == user)"
 *          }
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "access_control"="is_granted('ROLE_WRITER')"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "title": "partial",
 *          "content": "partial",
 *          "author": "exact",
 *          "author.name": "partial"
 *      }
 * )
 * @ApiFilter(
 *      RangeFilter::class,
 *      properties={
 *          "id"
 *      }
 * )
 * @ApiFilter(
 *      PropertyFilter::class,
 *      arguments={
 *          "parameterName"="properties",
 *          "overrideDefaultProperties": false,
 *          "whitelist": {"id", "author", "slug", "title", "content"}
 *      }
 * )
 * @ApiFilter(
 *      DateFilter::class,
 *      properties={
 *          "published"
 *      }
 * )
 * @ApiFilter(
 *      OrderFilter::class,
 *      properties={
 *          "id",
 *          "published",
 *          "title"
 *      },
 *      arguments={"orderParameterName"="_order"}
 * )
 */
class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get-blog-post-with-author"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min=10
     * )
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get-blog-post-with-author"})
     */
    private $published;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min=20,
     *      max=3000
     * )
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-blog-post-with-author", "get-blog-post-with-author"})
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
     * @Groups({"get-blog-post-with-author"})
     * @var $comments
     */
    private $comments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Image", )
     * @ORM\JoinTable()
     * @ApiSubresource()
     * @Groups({"post", "get-blog-post-with-author"})
     */
    private $images;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function getAuthor ():?User
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
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return Collection
     */ 
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        $this->images->add($image);
        return $this;
    }

    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
