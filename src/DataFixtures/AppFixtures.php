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

    private const USERS = [
        [
            "username"  =>  'admin',
            "email"     =>  'admin@blog.com',
            "name"      =>  'Misael Burboa',
            "password"  =>  'DevGuy123'
        ],
        [
            "username"  =>  'jhon_doe',
            "email"     =>  'jhon@blog.com',
            "name"      =>  'Jhon Doe',
            "password"  =>  'DevGuy123'
        ],
        [
            "username"  =>  'rob_smith',
            "email"     =>  'rob@blog.com',
            "name"      =>  'Rob Smith',
            "password"  =>  'DevGuy123'
        ],
        [
            "username"  =>  'jenny_rowling',
            "email"     =>  'jenny@blog.com',
            "name"      =>  'Jenny Rowling',
            "password"  =>  'DevGuy123'
        ],
    ];

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

            $authorReference = $this->getRandomUserReference();
            $blogPost->setAuthor($authorReference);
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

                $authorReference = $this->getRandomUserReference();
                $comment->setAuthor($authorReference);
                $comment->setBlogPost($this->getReference("blog_post_$i"));

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userFixture) {
            $user = new User();
            $user->setUsername($userFixture['username']);
            $user->setEmail($userFixture['email']);
            $user->setName($userFixture['name']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $userFixture['password']
            ));

            $this->addReference('user_'.$userFixture['username'], $user);
            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @return User
     */
    protected function getRandomUserReference():User
    {
        return $this->getReference('user_'.self::USERS[rand(0, 3)]['username']);
    }

}
