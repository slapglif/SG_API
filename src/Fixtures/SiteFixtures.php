<?php


namespace App\Fixtures;

use App\Entity\Site;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $s = new Site();
            $s->setActive(1);
            $s->setCreationDate($this->date = new DateTime());
            $s->setDescription('Description for site:'.$i);
            $s->setName('Site'.$i);
            $s->setTapFrequency(random_int(10, 90));
            $this->setReference('site'.$i, $s);
            $manager->persist($s);
        }
        $manager->flush();
    }
}