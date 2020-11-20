<?php

namespace App\Fixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'user';
    protected $faker;

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $u = new User();
        $u->setFirstName('George');
        $u->setLastName('Farrell');
        $u->setEmail('george@farrelltech.org');
        $u->setPassword($this->encoder->encodePassword($u, 'password'));
        $u->setRoles(['ROLE_FARRELL_TECH']);
        $u->setActive(1);
        $u->setRegistrationDate($this->date = new DateTime());
        $u->setLastLoggedIn($this->date = new DateTime());
        $this->setReference('admin', $u);
        $manager->persist($u);

        for ($i = 0; $i < 20; $i++) {
            $q = new User();
            $q->setFirstName($this->faker->firstName);
            $q->setLastName($this->faker->lastName);
            $q->setEmail($this->faker->email);
            $q->setPassword($this->encoder->encodePassword($u, 'password'));
            $q->setRoles(['ROLE_GUARD']);
            $q->setActive(1);
            $q->setRegistrationDate($this->faker->dateTime());
            $q->setLastLoggedIn($this->date = new DateTime());
            $this->setReference('user'.$i, $q);

            $manager->persist($q);
        }

        $manager->flush();
    }
}