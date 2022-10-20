<?php

namespace App\DataFixtures;

use App\Entity\Gandalf;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@gandoulfapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        $userAdmin = new User();
        $userAdmin->setEmail("admin@gandoulfapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        $gandalf = new Gandalf;
        $gandalf->setTitle("Laying on the floor");
        $gandalf->setComment("le mignon petit chat");
        $gandalf->setUrl("https://github.com/tdassise/pics-gandoulf/blob/origin/laying_on_the_floor.jpg");
        $manager->persist($gandalf);

        $gandalfo = new Gandalf;
        $gandalfo->setTitle("ehrgeto");
        $gandalf->setComment("le miefzgnon peuirgio cfreghat");
        $gandalfo->setUrl("https://github.com/tdassise/pics-gandoulf/blob/origin/laying_on_the_flofffor.jpg");
        $manager->persist($gandalfo);

        $manager->flush();
    }
}
