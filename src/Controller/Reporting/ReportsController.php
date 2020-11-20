<?php

namespace App\Controller\Reporting;

use App\Services\ReportHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Author farrelltechnology
 * Class ReportsController
 * @package App\Controller\Reporting
 * @Route("/api/")
 * @IsGranted({"ROLE_ADMIN"})
 */
class ReportsController extends AbstractFOSRestController
{
    /**
     * @Route("report/deleted", name="deleted_reports", methods="POST")
     * @param SerializerInterface $serializer
     * @param ReportHelper $reportHelper
     * @param Request $request
     * @return JsonResponse
     */
    public function index(SerializerInterface $serializer, ReportHelper $reportHelper, Request $request)
    {
        $startDate = Date($request->request->get('startDate'));
        $endDate = Date($request->request->get('endDate'));
        $entity = $request->request->get('entity');
        $site = $request->request->get('site');
        $company = $this->getUser()->getCompany();

        foreach (explode(",", $site) as $value) {
            $arr[] = $reportHelper->getSoftDeletes($startDate, $endDate, $value, $entity);
        }

        $data = $serializer->serialize($arr, 'json', SerializationContext::create()->enableMaxDepthChecks());

        return new JsonResponse($data, Response::HTTP_ACCEPTED, [], true);
    }

    /**
     * @IsGranted("ROLE_SITE_ADMIN")
     * @Route("report/deleted/xls", name="deleted_reports_csv", methods="POST")
     * @param ReportHelper $reportHelper
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function indexCSV(ReportHelper $reportHelper, Request $request)
    {
        $startDate = Date($request->request->get('startDate'));
        $endDate = Date($request->request->get('endDate'));
        $entity = $request->request->get('entity');
        $site = $request->request->get('site');
        $company = $this->getUser()->getCompany();

        foreach (explode(",", $site) as $value) {
            $arr[] = $reportHelper->getSoftDeletes($startDate, $endDate, $value, $entity);
        }

        $test = $reportHelper->templateDeletedXlsx($arr, $entity, $company);

        return $this->file($test, 'test.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
