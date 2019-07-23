<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BlogPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Comment;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \Faker\Factory $faker
     */
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
    }
    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        $user = $this->getReference('user_admin');

        for($i = 0; $i<100; $i++) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->faker->realText(30));
            $blogPost->setPublished($this->faker->dateTimeThisYear);
            $blogPost->setContent($this->faker->realText());
            $blogPost->setAuthor($user);
            $blogPost->setSlug($this->faker->slug);

            $this->setReference("blog_post_$i", $blogPost);

            $manager->persist($blogPost);

        }

        $manager->flush();
        
    }

    public function loadComments(ObjectManager $manager)
    {
        for($i=0; $i<100; $i++) {
            for($j=0; $j < rand(1,10); $j++){
                $comment = new Comment();
                $comment->setContent($this->faker->realText());
                $comment->setPublished($this->faker->dateTimeThisYear);
                $comment->setAuthor($this->getReference('user_admin'));
                $comment->setBlogPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername("cmburboa");
        $user->setEmail("cmburboa@gmail.com");
        $user->setName("Misael Burboa");
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'asdcom123'
        ));

        $this->addReference('user_admin', $user);

        $manager->persist($user);
        $manager->flush();
    }

}
