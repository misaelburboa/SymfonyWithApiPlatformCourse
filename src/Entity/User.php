<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Controller\ResetPasswordAction;

/**
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *                  "normalization_context"={
 *                      "groups"={"get"}
 *                  }
 *          },
 *          "put"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "denormalization_context"={
 *                  "groups"={"put"}
 *              },
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              }
 *          },
 *          "put-reset-password"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "method"="PUT",
 *              "path"="/users/{id}/reset-password",
 *              "controller"=ResetPasswordAction::class,
 *              "denormalization_context"={
 *                  "groups"={"put-reset-password"}
 *              }
 *          },
 *      },
 *      collectionOperations={
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={"post"}
 *              },
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              }
 *          }
 *      }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface
{
    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post", "get-comment-with-author", "get-blog-post-with-author"})
     * @Assert\NotBlank()
     * @Assert\Length(min=6, max=255, groups={"post"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"post"})
     * @Assert\Regex(
     *      pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *      message="Password must be seven characters long and contain at least one digit, one uppercase letter and one
     *              lower case letter",
     *      groups={"post"}
     * )
     */
    private $password;

    /**
     * @Groups({"post"})
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Expression(
     *      "this.getPassword() === this.getRetypedPassword()",
     *      message="Passwords does not match",
     *      groups={"post"}
     * )
     */
    private $retypedPassword;

    /**
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Groups({"put-reset-password"})
     * @Assert\Regex(
     *      pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *      message="Password must be seven characters long and contain at least one digit, one uppercase letter and one
     *              lower case letter",
     *      groups={"put-reset-password"}
     * )
     */
    private $newPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Assert\Expression(
     *      "this.getNewPassword() === this.getNewRetypedPassword()",
     *      message="Passwords does not match",
     *      groups={"put-reset-password"}
     * )
     */
    private $newRetypedPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @UserPassword(groups={"put-reset-password"})
     */
    private $oldPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post", "put", "get-comment-with-author", "get-blog-post-with-author"})
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min=6, max=255, groups={"post", "put"})
     */
    private $name;

    /**
     * @Groups({"post", "put", "get-admin", "get-owner"})
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email(groups={"post", "put"})
     * @Assert\Length(min=6, max=255, groups={"post", "put"})
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"get"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"get"})
     */
    private $comments;

    /**
     * @ORM\Column(type="simple_array", length=200)
     * @Groups({"get-admin", "get-owner"})
     */
    private $roles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordChangeDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $confirmationToken;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
        $this->enabled = false;
        $this->confirmationToken = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }

    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    public function setRetypedPassword($retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return  self
     */ 
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     * @return  self
     */ 
    public function setNewPassword($newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    /**
     * @param string $newRetypedPassword
     * @return  self
     */ 
    public function setNewRetypedPassword($newRetypedPassword):self
    {
        $this->newRetypedPassword = $newRetypedPassword;

        return $this;
    }

    /**
     * @return string
     */ 
    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    /**
     * @param string $oldPassword
     * @return  self
     */ 
    public function setOldPassword($oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }

    /**
     * @return  self
     */ 
    public function setPasswordChangeDate($passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }


    public function setEnabled($enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken):self
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
