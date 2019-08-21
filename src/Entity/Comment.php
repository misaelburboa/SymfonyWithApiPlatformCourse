<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\BlogPost;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\AuthoredEntityInterface;
use App\Entity\PublishedDateEntityInterface;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *      attributes={
 *          "order"={"published": "DESC"},
 *          "pagination_client_enabled"=true,
 *          "pagination_client_items_per_page"=2
 *      },
 *      denormalizationContext={
 *          "groups"={"post"}
 *      },
 *      itemOperations={
 *          "get",
 *          "put"={
 *              "access_control"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_COMMENTATOR') and object.getAuthor() == user)"
 *          }
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "access_control"="is_granted('ROLE_COMMENTATOR')"
 *          }
 *      },
 *      subresourceOperations={
 *         "api_blog_posts_comments_get_subresource"={
 *             "normalization_context"={
 *                 "groups"={"get-comment-with-author"}
 *             }
 *         }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get-comment-with-author"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"post", "get-comment-with-author"})
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get-comment-with-author"})
     */
    private $published;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-comment-with-author"})
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BlogPost", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"post"})
     * @var $blogPost
     */
    private $blogPost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function setAuthor(UserInterface $author):AuthoredEntityInterface
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthor () :?User
    {
        return $this->author;
    }

    /**
     * @return  BlogPost $blogPost
     */ 
    public function getBlogPost(): ?BlogPost
    {
        return $this->blogPost;
    }

    /**
     * @param  $blogPost  $blogPost
     *
     * @return  self
     */ 
    public function setBlogPost(BlogPost $blogPost)
    {
        $this->blogPost = $blogPost;

        return $this;
    }

    public function __toString()
    {
        return substr($this->content, 0, 20).'...';
    }
}
