<?php


namespace App\Fixtures;


use App\Entity\GuardShift;
use App\Services\Timestamp;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GuardFixtures extends Fixture implements DependentFixtureInterface
{

    private $timestamp;

    public function __construct(Timestamp $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $randomStartDate = rand(1588291200, 1590969599);

            $s = new GuardShift();
            $s->setSite($this->getReference('site'.rand(1, 19)));
            $s->setAdmin($this->getReference('admin'));
            $s->setUser($this->getReference('user'.rand(1, 19)));
            $s->setShiftStart($this->timestamp->reverseTransform($randomStartDate));
            $s->setShiftEnd($this->timestamp->reverseTransform(rand($randomStartDate, 1590969599)));
            $manager->persist($s);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return ([
            UserFixtures::class,
            SiteFixtures::class,
        ]);
    }
}