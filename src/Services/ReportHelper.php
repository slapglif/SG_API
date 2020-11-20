<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpKernel\KernelInterface;

class ReportHelper
{
    /**
     * @var string
     */
    protected $projectDir;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EntityManagerInterface $entityManager,
        KernelInterface $kernel
    ) {
        $this->em = $entityManager;
        $this->projectDir = $kernel->getProjectDir();
    }

    public function getSoftDeletes($startDate, $endDate, $site, $entity)
    {
        if ($entity == null) {
            $pattern = '/App\\\\Entity\\\\/';
            $meta = $this->em->getMetadataFactory()->getAllMetadata();
            dump($meta);
            foreach ($meta as $m) {
                if (preg_match($pattern, $m->getName())) {
                    $test = preg_replace($pattern, 'App:', $m->getName());
                    $data[] = $this->em->getRepository($test)->findByDeleted($startDate, $endDate, $site);
                    $goodNames[] = $m->getName();
                } else {
                    $badNames[] = $m->getName();
                }
            }
            return $data;
        } else {
            return $this->em->getRepository('App:'.$entity)->findByDeleted($startDate, $endDate, $site);
        }
    }

    public function templateDeletedXlsx($info, $entity, $company)
    {
        switch ($entity) {
            case 'CheckPoint':
                return $this->deletedCheckpoint($info, $company);
                break;
            case 'Site':
                return $this->deletedSite($info, $company);
                break;
            case 'User':
                return $this->deletedUser($info, $company);
                break;
            case 'Perimeter':
                return $this->deletedPerimeter($info, $company);
                break;
            case 'GuardShift':
                return $this->deletedGuardShift($info, $company);
                break;
            case 'CheckpointInteraction':
                return $this->deletedCheckPointInteraction($info, $company);
                break;
        }
    }

    private function deletedCheckpoint($info,$company)
    {
        $webPath = $this->projectDir;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setHeaders($sheet);

        $sheet->setCellValue('F1', 'Deleted Checkpoints');
        $sheet->setCellValue('F3', $company->getCompanyName());

        $spreadsheet->getActiveSheet()->mergeCells('A1:D6');
        $drawing = new Drawing();
        $drawing->setName('Logo');
        try {
            $drawing->setPath($webPath.$company->getCompanyLogo());
        } catch (Exception $e) {
        }
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWidth(440);
        $drawing->setCoordinates('A1');

        $sheet->getStyle('A1:D6')->getAlignment()->setHorizontal('center');
//        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($style);

        $row = 6;
        $siteName = '';

        foreach ($info as $value) {
            for ($i = 0; $i <= count($value) - 1; $i++) {
                $checkpoint = $value[$i];

                if ($siteName == $checkpoint->getSite()->getName()) {
                    $siteName = $checkpoint->getSite()->getName();

                    $sheet->setCellValue('A'.$row, $checkpoint->getId());
                    $sheet->setCellValue('B'.$row, $checkpoint->getName());
                    $sheet->setCellValue('C'.$row, $checkpoint->getSite()->getName());
                    $sheet->setCellValue('D'.$row, $checkpoint->getAssetId());
                    $sheet->setCellValue('E'.$row, $checkpoint->getLocationInformation());
                    $sheet->setCellValue('F'.$row, $checkpoint->getLatitude());
                    $sheet->setCellValue('G'.$row, $checkpoint->getLongitude());
                    $sheet->setCellValue('H'.$row, $checkpoint->getActive());
                    $sheet->setCellValue('I'.$row, $checkpoint->getDeletedAt());
                    $sheet->setCellValue('J'.$row, count($checkpoint->getCheckpointInteractions()));
                } else {
                    $siteName = $checkpoint->getSite()->getName();
                    $row = $row + 3;
                    $sheet->setCellValue(
                        'A'.$row,
                        'Site Name: '.$checkpoint->getSite()->getName()
                    );
                    $row++;
                    $sheet->setCellValue('A'.$row, 'ID');
                    $sheet->setCellValue('B'.$row, 'Checkpoint Name');
                    $sheet->setCellValue('C'.$row, 'Site');
                    $sheet->setCellValue('D'.$row, 'Asset ID');
                    $sheet->setCellValue('E'.$row, 'Location Desc');
                    $sheet->setCellValue('F'.$row, 'Latitude');
                    $sheet->setCellValue('G'.$row, 'Longitude');
                    $sheet->setCellValue('H'.$row, 'Active');
                    $sheet->setCellValue('I'.$row, 'Deleted');
                    $sheet->setCellValue('J'.$row, 'Interactions');
                    $row++;

                    $sheet->setCellValue('A'.$row, $checkpoint->getId());
                    $sheet->setCellValue('B'.$row, $checkpoint->getName());
                    $sheet->setCellValue('C'.$row, $checkpoint->getSite()->getName());
                    $sheet->setCellValue('D'.$row, $checkpoint->getAssetId());
                    $sheet->setCellValue('E'.$row, $checkpoint->getLocationInformation());
                    $sheet->setCellValue('F'.$row, $checkpoint->getLatitude());
                    $sheet->setCellValue('G'.$row, $checkpoint->getLongitude());
                    $sheet->setCellValue('H'.$row, $checkpoint->getActive());
                    $sheet->setCellValue('I'.$row, $checkpoint->getDeletedAt());
                    $sheet->setCellValue('J'.$row, count($checkpoint->getCheckpointInteractions()));

                }
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $finalFileName = sprintf(
            '%s_%s.%s',
            uniqid(),
            md5($writer->getDiskCachingDirectory()),
            'Xlsx'
        );

        $savePath = $webPath.'/data/reports/xls/'.$finalFileName;
        $writer->save($savePath);

        return $savePath;
    }

    private function deletedSite($info,$company)
    {        $webPath = $this->projectDir;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setHeaders($sheet);

        $sheet->setCellValue('F1', 'Deleted Checkpoints');
        $sheet->setCellValue('F3', $company->getCompanyName());

        $spreadsheet->getActiveSheet()->mergeCells('A1:D6');
        $drawing = new Drawing();
        $drawing->setName('Logo');
        try {
            $drawing->setPath($webPath.$company->getCompanyLogo());
        } catch (Exception $e) {
        }
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWidth(440);
        $drawing->setCoordinates('A1');

        $sheet->getStyle('A1:D6')->getAlignment()->setHorizontal('center');
//        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($style);

        $row = 6;
        $siteName = '';

        foreach ($info as $value) {
            for ($i = 0; $i <= count($value) - 1; $i++) {
                $site = $value[$i];

                if ($siteName == $site->getName()) {
                    $siteName = $site->getName();

                    $sheet->setCellValue('A'.$row, $site->getId());
                    $sheet->setCellValue('B'.$row, $site->getName());
                    $sheet->setCellValue('C'.$row, $site->getCreationDate());
                    $sheet->setCellValue('D'.$row, $site->getActive());
                    $sheet->setCellValue('E'.$row, $site->getDescription());
                    $sheet->setCellValue('F'.$row, $site->getTapFrequency());
                    $sheet->setCellValue('G'.$row, $site->getDeletedAt());

                } else {
                    $siteName = $site->getSite()->getName();
                    $row = $row + 3;
                    $sheet->setCellValue(
                        'A'.$row,
                        'Site Name: '.$site->getName()
                    );
                    $row++;
                    $sheet->setCellValue('A'.$row, 'ID');
                    $sheet->setCellValue('B'.$row, 'Site Name');
                    $sheet->setCellValue('C'.$row, 'Creation Date');
                    $sheet->setCellValue('D'.$row, 'Active');
                    $sheet->setCellValue('E'.$row, 'Description');
                    $sheet->setCellValue('F'.$row, 'Tap Frequency');
                    $sheet->setCellValue('G'.$row, 'Deleted At');
                    $row++;

                    $sheet->setCellValue('A'.$row, $site->getId());
                    $sheet->setCellValue('B'.$row, $site->getName());
                    $sheet->setCellValue('C'.$row, $site->getCreationDate());
                    $sheet->setCellValue('D'.$row, $site->getActive());
                    $sheet->setCellValue('E'.$row, $site->getDescription());
                    $sheet->setCellValue('F'.$row, $site->getTapFrequency());
                    $sheet->setCellValue('G'.$row, $site->getDeletedAt());


                }
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $finalFileName = sprintf(
            '%s_%s.%s',
            uniqid(),
            md5($writer->getDiskCachingDirectory()),
            'Xlsx'
        );

        $savePath = $webPath.'/data/reports/xls/'.$finalFileName;
        $writer->save($savePath);

        return $savePath;
    }

    private function deletedUser($info,$company)
    {
        $webPath = $this->projectDir;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setHeaders($sheet);

        $sheet->setCellValue('F1', 'Deleted Users');
        $sheet->setCellValue('F3', $company->getCompanyName());

        $spreadsheet->getActiveSheet()->mergeCells('A1:D6');
        $drawing = new Drawing();
        $drawing->setName('Logo');
        try {
            $drawing->setPath($webPath.$company->getCompanyLogo());
        } catch (Exception $e) {
        }
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWidth(440);
        $drawing->setCoordinates('A1');

        $sheet->getStyle('A1:D6')->getAlignment()->setHorizontal('center');
//        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($style);

        $row = 7;
        // TODO: Deleted User - company only

        foreach ($info as $value) {
            $sheet->setCellValue('A'.$row, 'ID');
            $sheet->setCellValue('B'.$row, 'E-Mail');
            $sheet->setCellValue('C'.$row, 'Roles');
            $sheet->setCellValue('D'.$row, 'Name');
            $sheet->setCellValue('E'.$row, 'Last Logged In');
            $sheet->setCellValue('F'.$row, 'Registration Date');
            $sheet->setCellValue('G'.$row, 'Deleted At');

            for ($i = 0; $i <= count($value) - 1; $i++) {
                $user = $value[$i];
                $row++;
                    $sheet->setCellValue('A'.$row, $user->getId());
                    $sheet->setCellValue('B'.$row, $user->getEmail());
//            $sheet->setCellValue('C' . $row, $user->getRoles());
                    $sheet->setCellValue('D'.$row, $user->getFirstName().' '.$user->getLastName());
                    $sheet->setCellValue('E'.$row, $user->getLastLoggedIn());
                    $sheet->setCellValue('F'.$row, $user->getRegistrationDate());
                    $sheet->setCellValue('G'.$row, $user->getActive());
                    $sheet->setCellValue('H'.$row, $user->getDeletedAt());
                }
            }

        $writer = new Xlsx($spreadsheet);

        $finalFileName = sprintf(
            '%s_%s.%s',
            uniqid(),
            md5($writer->getDiskCachingDirectory()),
            'Xlsx'
        );

        $savePath = $webPath.'/data/reports/xls/'.$finalFileName;
        $writer->save($savePath);

        return $savePath;
    }

    private function deletedPerimeter($info, $company)
    {
        $webPath = $this->projectDir;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setHeaders($sheet);

        $sheet->setCellValue('F1', 'Deleted Shifts');
        $sheet->setCellValue('F3', $company->getCompanyName());

        $spreadsheet->getActiveSheet()->mergeCells('A1:D6');
        $drawing = new Drawing();
        $drawing->setName('Logo');
        try {
            $drawing->setPath($webPath.$company->getCompanyLogo());
        } catch (Exception $e) {
        }
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWidth(440);
        $drawing->setCoordinates('A1');

        $sheet->getStyle('A1:D6')->getAlignment()->setHorizontal('center');
        $row = 6;
        $siteName = '';

        foreach ($info as $value) {
            for ($i = 0; $i <= count($value) - 1; $i++) {
                $perimeter = $value[$i];

                if ($siteName == $perimeter->getSite()->getName()) {
                    $siteName = $perimeter->getSite()->getName();

                    $sheet->setCellValue('A'.$row, $perimeter->getId());
                    $sheet->setCellValue('B'.$row, $perimeter->getRefNumber());
                    $sheet->setCellValue('C'.$row, $perimeter->getLatitude());
                    $sheet->setCellValue('D'.$row, $perimeter->getLongitude());
                    $sheet->setCellValue('E'.$row, $perimeter->getDeletedAt());
                } else {
                    $siteName = $perimeter->getSite()->getName();
                    $row = $row + 3;
                    $sheet->setCellValue(
                        'A'.$row,
                        'Site Name: '. $perimeter->getSite()->getName()
                    );
                    $row++;
                    $sheet->setCellValue('A'.$row, 'ID');
                    $sheet->setCellValue('B'.$row, 'Reference Id');
                    $sheet->setCellValue('C'.$row, 'Latitude');
                    $sheet->setCellValue('D'.$row, 'Longitude');
                    $sheet->setCellValue('E'.$row, 'Deleted At');
                    $row++;

                    $sheet->setCellValue('A'.$row, $perimeter->getId());
                    $sheet->setCellValue('B'.$row, $perimeter->getRefNumber());
                    $sheet->setCellValue('C'.$row, $perimeter->getLatitude());
                    $sheet->setCellValue('D'.$row, $perimeter->getLongitude());
                    $sheet->setCellValue('E'.$row, $perimeter->getDeletedAt());
                }
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $finalFileName = sprintf(
            '%s_%s.%s',
            uniqid(),
            md5($writer->getDiskCachingDirectory()),
            'Xlsx'
        );

        $savePath = $webPath.'/data/reports/xls/'.$finalFileName;
        $writer->save($savePath);

        return $savePath;
    }

    private function deletedGuardShift($info,$company)
    {
        $webPath = $this->projectDir;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setHeaders($sheet);

        $sheet->setCellValue('F1', 'Deleted Shifts');
        $sheet->setCellValue('F3', $company->getCompanyName());

        $spreadsheet->getActiveSheet()->mergeCells('A1:D6');
        $drawing = new Drawing();
        $drawing->setName('Logo');
        try {
            $drawing->setPath($webPath.$company->getCompanyLogo());
        } catch (Exception $e) {
        }
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWidth(440);
        $drawing->setCoordinates('A1');

        $sheet->getStyle('A1:D6')->getAlignment()->setHorizontal('center');
        $row = 6;
        $siteName = '';

        foreach ($info as $value) {
            for ($i = 0; $i <= count($value) - 1; $i++) {
                $guardShift = $value[$i];

                if ($siteName == $guardShift->getSite()->getName()) {
                    $siteName = $guardShift->getSite()->getName();

                    $sheet->setCellValue('A'.$row, $guardShift->getId());
                    $sheet->setCellValue(
                        'B'.$row,
                        $guardShift->getUser()->getFirstName().' '.$guardShift->getUser()->getLastName()
                    );
                    $sheet->setCellValue(
                        'C'.$row,
                        $guardShift->getAdmin()->getFirstName().' '.$guardShift->getAdmin()->getLastName()
                    );
                    $sheet->setCellValue('D'.$row, $guardShift->getSite()->getName());
                    $sheet->setCellValue('E'.$row, $guardShift->getShiftStart());
                    $sheet->setCellValue('F'.$row, $guardShift->getShiftEnd());
                    $sheet->setCellValue('G'.$row, $guardShift->getActualShiftStart());
                    $sheet->setCellValue('H'.$row, $guardShift->getActualShiftEnd());
                } else {
                    $siteName = $guardShift->getSite()->getName();
                    $row = $row + 3;
                    $sheet->setCellValue(
                        'A'.$row,
                        'Site Name: '.$guardShift->getSite()->getName()
                    );
                    $row++;
                    $sheet->setCellValue('A'.$row, 'ID');
                    $sheet->setCellValue('B'.$row, 'Guards Name');
                    $sheet->setCellValue('C'.$row, 'Admin Name');
                    $sheet->setCellValue('D'.$row, 'Site Name');
                    $sheet->setCellValue('E'.$row, 'Scheduled Start');
                    $sheet->setCellValue('F'.$row, 'Scheduled Finish');
                    $sheet->setCellValue('G'.$row, 'Realtime Start');
                    $sheet->setCellValue('H'.$row, 'Realtime Finish');
                    $row++;

                    $sheet->setCellValue('A'.$row, $guardShift->getId());
                    $sheet->setCellValue(
                        'B'.$row,
                        $guardShift->getUser()->getFirstName().' '.$guardShift->getUser()->getLastName()
                    );
                    $sheet->setCellValue(
                        'C'.$row,
                        $guardShift->getAdmin()->getFirstName().' '.$guardShift->getAdmin()->getLastName()
                    );
                    $sheet->setCellValue('D'.$row, $guardShift->getSite()->getName());
                    $sheet->setCellValue('E'.$row, $guardShift->getShiftStart());
                    $sheet->setCellValue('F'.$row, $guardShift->getShiftEnd());
                    $sheet->setCellValue('G'.$row, $guardShift->getActualShiftStart());
                    $sheet->setCellValue('H'.$row, $guardShift->getActualShiftEnd());
                }
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $finalFileName = sprintf(
            '%s_%s.%s',
            uniqid(),
            md5($writer->getDiskCachingDirectory()),
            'Xlsx'
        );

        $savePath = $webPath.'/data/reports/xls/'.$finalFileName;
        $writer->save($savePath);

        return $savePath;
    }

    private function deletedCheckPointInteraction($info, $company)
    {
        $webPath = $this->projectDir;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setHeaders($sheet);

        $sheet->setCellValue('F1', 'Deleted Checkpoints');
        $sheet->setCellValue('F3', $company->getCompanyName());

        $spreadsheet->getActiveSheet()->mergeCells('A1:D6');
        $drawing = new Drawing();
        $drawing->setName('Logo');
        try {
            $drawing->setPath($webPath.$company->getCompanyLogo());
        } catch (Exception $e) {
        }
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWidth(440);
        $drawing->setCoordinates('A1');

        $sheet->getStyle('A1:D6')->getAlignment()->setHorizontal('center');
//        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($style);

        $row = 6;
        $siteName = '';

        foreach ($info as $value) {
            for ($i = 0; $i <= count($value) - 1; $i++) {
                $checkpointInteraction = $value[$i];

                if ($siteName == $checkpointInteraction->getShift()->getSite()->getName()) {
                    $siteName = $checkpointInteraction->getShift()->getSite()->getName();

                    $sheet->setCellValue('A'.$row, $checkpointInteraction->getId());
                    $sheet->setCellValue('B'.$row, $checkpointInteraction->getCheckpoint()->getName());
                    $sheet->setCellValue(
                        'C'.$row,
                        $checkpointInteraction->getUser()->getFirstName().' '.$checkpointInteraction->getUser(
                        )->getLastName()
                    );
                    $sheet->setCellValue('D'.$row, $checkpointInteraction->getSubmitted());
                    $sheet->setCellValue('E'.$row, $checkpointInteraction->getLive());
                    $sheet->setCellValue('F'.$row, $checkpointInteraction->getShift()->getId());
                    $sheet->setCellValue('G'.$row, $checkpointInteraction->getDeletedAt());
                } else {
                    $siteName = $checkpointInteraction->getShift()->getSite()->getName();
                    $row = $row + 3;
                    $sheet->setCellValue(
                        'A'.$row,
                        'Site Name: '.$checkpointInteraction->getShift()->getSite()->getName()
                    );
                    $row++;
                    $sheet->setCellValue('A'.$row, 'ID');
                    $sheet->setCellValue('B'.$row, 'Checkpoint Name');
                    $sheet->setCellValue('C'.$row, 'User');
                    $sheet->setCellValue('D'.$row, 'Time');
                    $sheet->setCellValue('E'.$row, 'Live');
                    $sheet->setCellValue('F'.$row, 'Shift #');
                    $sheet->setCellValue('G'.$row, 'Deleted At');
                    $row++;

                    $sheet->setCellValue('A'.$row, $checkpointInteraction->getId());
                    $sheet->setCellValue('B'.$row, $checkpointInteraction->getCheckpoint()->getName());
                    $sheet->setCellValue(
                        'C'.$row,
                        $checkpointInteraction->getUser()->getFirstName().' '.$checkpointInteraction->getUser(
                        )->getLastName()
                    );
                    $sheet->setCellValue('D'.$row, $checkpointInteraction->getSubmitted());
                    $sheet->setCellValue('E'.$row, $checkpointInteraction->getLive());
                    $sheet->setCellValue('F'.$row, $checkpointInteraction->getShift()->getId());
                    $sheet->setCellValue('G'.$row, $checkpointInteraction->getDeletedAt());
                }
                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $finalFileName = sprintf(
            '%s_%s.%s',
            uniqid(),
            md5($writer->getDiskCachingDirectory()),
            'Xlsx'
        );

        $savePath = $webPath.'/data/reports/xls/'.$finalFileName;
        $writer->save($savePath);

        return $savePath;
    }

    public function setHeaders($sheet)
    {
        $date = date_create()->format('d.m.Y');

        $sheet->setCellValue('E1', "Report Name");
        $sheet->setCellValue('E2', "Date");
        $sheet->setCellValue('F2', $date);
        $sheet->setCellValue('E3', "Company");

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        return $sheet;
    }

    function center()
    {
        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );

        return $style;
    }
}
