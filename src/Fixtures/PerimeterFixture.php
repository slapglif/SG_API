<?php


namespace App\Fixtures;


use App\Entity\Perimeter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PerimeterFixture extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $s = new Perimeter();
        $s->setSite($this->getReference('site1'));
        $s->setRefNumber(1);
        $s->setLatitude(51.800587);
        $s->setLongitude(-0.217726);
        $manager->persist($s);

        $a = new Perimeter();
        $s->setSite($this->getReference('site1'));
        $a->setRefNumber(2);
        $a->setLatitude(51.798332);
        $a->setLongitude(-0.219789);
        $manager->persist($a);

        $b = new Perimeter();
        $s->setSite($this->getReference('site1'));
        $b->setRefNumber(3);
        $b->setLatitude(51.797051);
        $b->setLongitude(-0.216408);
        $manager->persist($b);

        $c = new Perimeter();
        $s->setSite($this->getReference('site1'));
        $c->setRefNumber(4);
        $c->setLatitude(51.800090);
        $c->setLongitude(-0.215109);
        $manager->persist($c);

        $manager->flush();
    }

    public function getDependencies()
    {
        return ([
            SiteFixtures::class,
        ]);
    }
}