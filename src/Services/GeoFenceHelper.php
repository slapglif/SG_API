<?php

namespace App\Services;

use App\Repository\PerimeterRepository;
use Location\Coordinate;
use Location\Polygon;

class GeoFenceHelper
{
    /**
     * @var PerimeterRepository
     */
    private $perimeterRepository;

    public function __construct(PerimeterRepository $perimeterRepository)
    {
        $this->perimeterRepository = $perimeterRepository;
    }

    public function perimeterCheck($siteId, $currentLocation)
    {
        $site = $this->perimeterRepository->findBy(['site' => $siteId]);

        $geofence = new Polygon();
        foreach ($site as $key => $perimeter) {
            $geofence->addPoint(new Coordinate($perimeter->getLatitude(), $perimeter->getLongitude()));
        }

        return $geofence->contains($currentLocation);
    }

    public function getCentroid($siteId)
    {
        $lat = 0.0;
        $lon = 0.0;

        $site = $this->perimeterRepository->findBy(['site' => $siteId]);

        $geofence = new Polygon();
        foreach ($site as $key => $perimeter) {
            $geofence->addPoint(new Coordinate($perimeter->getLatitude(), $perimeter->getLongitude()));
            $lat += $perimeter->getLatitude();
            $lon += $perimeter->getLongitude();
        }

        $centerlat = $lat / $geofence->getNumberOfPoints();
        $centerlon = $lon / $geofence->getNumberOfPoints();

        return array($centerlat, $centerlon);
    }

//    public function getPerimeterPoints($siteId)
//    {
//        $site = $this->entityManager->findBy(['site' => $siteId]);
//        $point = [];
//
//        $geofence = new Polygon();
//        foreach ($site as $key => $perimeter) {
//            $geofence->addPoint(new Coordinate($perimeter->getLatitude(), $perimeter->getLongitude()));
//        }
//
//        dump($geofence);
//        die();
//
//        return array($geofence->getPoints());
//    }
}