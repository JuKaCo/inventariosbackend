<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Application\Actions\RepositoryConection\ConectBiometrico;
use App\Domain\RrhhRepository;
use \PDO;
use \DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use AbmmHasan\Uuid;

class DataRrhhRepository implements RrhhRepository {

    /**
     * @var $db conection db
     */
    private $db;

    /**
     * DataMenuRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
    }

    public function getReporteGeneral($data): array {

        if (!ini_get('date.timezone')) {
            date_default_timezone_set('GMT');
        }
        $jsonBody = $data;

        $fecini = $jsonBody['fini'];
        $fecfin = $jsonBody['ffin'];
        $date = DateTime::createFromFormat('d/m/Y', mb_convert_encoding($fecini, 'ISO-8859-1', 'UTF-8'));
        $fechaini = $date->format('Y-m-d');
        $date = DateTime::createFromFormat('d/m/Y', mb_convert_encoding($fecfin, 'ISO-8859-1', 'UTF-8'));
        $fechafin = $date->format('Y-m-d');
        $terminal_id = ($jsonBody['biometrico'])['id_param'];
        $hto=$jsonBody['hto'];

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
                ->setCreator("CEASS")
                ->setLastModifiedBy('') // última vez modificado por
                ->setTitle('Reporte ceass');
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);

        $sheet = $spreadsheet->getActiveSheet();




//    $sheet->mergeCells('A5:C5');
        $sheet->getStyle('A5:L5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('006e9d');
        $sheet->getStyle('A5:L5')->getFont()->getColor()->setARGB('ffffff');


        $sheet->setCellValue('A5', 'IMSS/CURP');
        $sheet->setCellValue('B5', 'NOMBRE');
        $sheet->setCellValue('C5', 'FECHA');
        $sheet->setCellValue('D5', 'HORARIO');
        $sheet->setCellValue('E5', 'ENTRADA');
        $sheet->setCellValue('F5', 'SALIDA');
        $sheet->setCellValue('G5', 'MARCADO ENTRADA');
        $sheet->setCellValue('H5', 'MARCADO SALIDA');
        $sheet->setCellValue('I5', 'TARDE');
        $sheet->setCellValue('J5', 'TEMPRANO');
        $sheet->setCellValue('K5', 'OBSERVACION');
        $cont = 6;
        try {
            $con = new ConectBiometrico();
            $db = $con->getConection();

            $sql = "
            SELECT *
            FROM hr_employee 
            WHERE id in (
                            SELECT  DISTINCT(emp_id)
                            FROM att_punches
                            WHERE terminal_id='$terminal_id' and punch_time between '$fechaini' and '$fechafin'
            )
               ";
            $tbl = $db->query($sql);
            $datos = $tbl->fetchAll(PDO::FETCH_ASSOC);
            foreach ($datos as $rowdat) {
                $emp_id=$rowdat['id'];
                $sql = "
                
		 SELECT DATE_FORMAT(a.fecha, '%d/%m/%Y') as fecha, IFNULL(b.horaver,'07:00') as horaE,TIME_FORMAT(b.marca,'%H:%i') as marcaE,IFNULL(c.horaver,'14:00') as horaS,TIME_FORMAT(c.marca,'%H:%i') as marcaS,'MAÑANA' AS turno,
                    IF(TIME_FORMAT(TIMEDIFF(TIME_FORMAT(b.marca,'%H:%i'),'07:00:'),'%H:%i')>'$hto',TIME_FORMAT(TIMEDIFF(TIME_FORMAT(b.marca,'%H:%i'),'07:00'),'%H:%i'),'') as retrasoE,
		   IF(TIMEDIFF('14:00',TIME_FORMAT(c.marca,'%H:%i'))>'00:00',TIMEDIFF('14:00',TIME_FORMAT(c.marca,'%H:%i')),'') as tempranoS
                    FROM (

                    SELECT selected_date as fecha from 
                    (select adddate('$fechaini',t4*10000 + t3*1000 + t2*100 + t1*10 + t0) selected_date from
                     (select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
                     (select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
                     (select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
                     (select 0 t3 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
                     (select 0 t4 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
                    where selected_date between '$fechaini' and '$fechafin' and DAYOFWEEK(selected_date) <> 1 and DAYOFWEEK(selected_date) <> 7
                    ORDER BY selected_date

                    ) a
                    LEFT JOIN
                    (
                    SELECT DATE(punch_time) as fecha,
                                    TIME(punch_time) as hora,
                                                                                    '07:00' as horaver,
                                                    MIN(TIME(punch_time)) as marca
                    FROM att_punches
                    WHERE time(punch_time)>='06:00' AND time(punch_time)<='10:59' AND emp_id=$emp_id and terminal_id='$terminal_id'
                    GROUP BY DATE(punch_time)
                    ORDER BY punch_time
                    )
                    b on (a.fecha=b.fecha)
                    LEFT JOIN
                    (SELECT DATE(punch_time) as fecha,
                                    TIME(punch_time) as hora,
                                                                                    '14:00' as horaver,
                                                    MAX(TIME(punch_time)) as marca
                    FROM att_punches
                    WHERE time(punch_time)>='10:01' AND time(punch_time)<='23:30' AND emp_id=$emp_id and terminal_id=2
                    GROUP BY DATE(punch_time)
                    ORDER BY punch_time)
                    c on (a.fecha=c.fecha)
                  ORDER BY a.fecha
               ";
         
                $sql2 = "
                    SELECT   TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(a.retrasoE))), '%H:%i' ) as sum1,
                        TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(a.tempranoS))), '%H:%i:%s' ) as sum2
from (

             		SELECT DATE_FORMAT(a.fecha, '%d/%m/%Y') as fecha, IFNULL(b.horaver,'07:00') as horaE,TIME_FORMAT(b.marca,'%H:%i') as marcaE,IFNULL(c.horaver,'14:00') as horaS,TIME_FORMAT(c.marca,'%H:%i') as marcaS,'MAÑANA' AS turno,
                    IF(TIME_FORMAT(TIMEDIFF(TIME_FORMAT(b.marca,'%H:%i'),'07:00:'),'%H:%i')>'$hto',TIME_FORMAT(TIMEDIFF(TIME_FORMAT(b.marca,'%H:%i'),'07:00'),'%H:%i'),'') as retrasoE,
		   IF(TIMEDIFF('14:00',TIME_FORMAT(c.marca,'%H:%i'))>'00:00',TIMEDIFF('14:00',TIME_FORMAT(c.marca,'%H:%i')),'') as tempranoS
                    FROM (

                    SELECT selected_date as fecha from 
                    (select adddate('$fechaini',t4*10000 + t3*1000 + t2*100 + t1*10 + t0) selected_date from
                     (select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
                     (select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
                     (select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
                     (select 0 t3 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
                     (select 0 t4 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
                    where selected_date between '$fechaini' and '$fechafin' and DAYOFWEEK(selected_date) <> 1 and DAYOFWEEK(selected_date) <> 7
                    ORDER BY selected_date

                    ) a
                    LEFT JOIN
                    (
                    SELECT DATE(punch_time) as fecha,
                                    TIME(punch_time) as hora,
                                                                                    '07:00' as horaver,
                                                    MIN(TIME(punch_time)) as marca
                    FROM att_punches
                    WHERE time(punch_time)>='06:00' AND time(punch_time)<='10:59' AND emp_id=$emp_id and terminal_id=2
                    GROUP BY DATE(punch_time)
                    ORDER BY punch_time
                    )
                    b on (a.fecha=b.fecha)
                    LEFT JOIN
                    (SELECT DATE(punch_time) as fecha,
                                    TIME(punch_time) as hora,
                                                                                    '14:00' as horaver,
                                                    MAX(TIME(punch_time)) as marca
                    FROM att_punches
                    WHERE time(punch_time)>='10:01' AND time(punch_time)<='23:30' AND emp_id=$emp_id and terminal_id=2
                    GROUP BY DATE(punch_time)
                    ORDER BY punch_time)
                    c on (a.fecha=c.fecha)
                         ) a


               ";

                $tbl = $db->query($sql);
                $datosper = $tbl->fetchAll(PDO::FETCH_ASSOC);
                $tbl2 = $db->query($sql2);
                $datos2 = $tbl2->fetchAll(PDO::FETCH_ASSOC);

           
                foreach ($datosper as $row1) {
                    $sheet->setCellValue('A' . $cont, '');
                    $sheet->setCellValue('B' . $cont, $rowdat['emp_firstname'] . ' ' . $rowdat['emp_lastname']);
                    $sheet->setCellValue('C' . $cont, $row1['fecha']);
                    $sheet->setCellValue('D' . $cont, $row1['turno']);
                    $sheet->setCellValue('E' . $cont, $row1['horaE']);
                    $sheet->setCellValue('F' . $cont, $row1['horaS']);
                    $sheet->setCellValue('G' . $cont, $row1['marcaE']);
                    $sheet->setCellValue('H' . $cont, $row1['marcaS']);
                    $sheet->setCellValue('I' . $cont, $row1['retrasoE']);
                    //$sheet->setCellValue('J' . $cont, $row1['retrasoS']);
                    $sheet->setCellValue('K' . $cont, ' ');
                    $cont++;
                }
                $sheet->setCellValue('H' . $cont, 'Sub Total:');

                $sheet->getStyle('A' . $cont . ':L' . $cont)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('3F7E06');
                $sheet->getStyle('A' . $cont . ':L' . $cont)->getFont()->getColor()->setARGB('ffffff');

                if (count($datos2) == 1) {
                    $datos2 = $datos2[0];
                    $sheet->setCellValue('I' . $cont, $datos2['sum1']);
                    $sheet->setCellValue('J' . $cont, $datos2['sum2']);
                }
                $cont++;
            }
        } catch (Exception $e) {
            ob_clean();
            $data = array('mensaje' => 'Error en ejecucion');
            $respuesta = array(
                'success' => false,
                'data' =>$e
            );
            $response = $response->withJson($respuesta, 202, JSON_PRETTY_PRINT);
            return $response;
        }
        $sheet->mergeCells('A4:L4');
        $sheet->setCellValue('A4', 'REPORTE BIOMETRICO INDIVIDUAL');
        $sheet->getStyle('A1:L4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
        $spreadsheet->getActiveSheet()->getStyle("A4")->getFont()->setSize(20);
        $cont--;
        $spreadsheet->getActiveSheet()->getStyle('A6:L' . $cont)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(43);
        $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(43);
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(43);
        $spreadsheet->getActiveSheet()->getRowDimension('4')->setRowHeight(30);

        //$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        //$drawing->setName('logo');
        //$drawing->setDescription('logo');
        //$drawing->setPath('images/logo.jpg'); // put your path and image here
        //$drawing->setCoordinates('A1');
        //$drawing->getShadow()->setVisible(true);
        //$drawing->getShadow()->setDirection(45);
        //$drawing->setWorksheet($spreadsheet->getActiveSheet());

        $writer = new Xlsx($spreadsheet);

        //$response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //$response = $response->withHeader('Content-Disposition', 'attachment; filename="rerpote_asistencia_concamyt.xlsx"');
        //$response->write($writer->save('php://output'));
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition : attachment; filename="rerpote_asistencia_ofcentral.xlsx"');
        
        
        //$response=array('data'=>$writer->save('php://output'));
        
        /*$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');

        $writer = new Xlsx($spreadsheet);*/
        $writer->save('php://output');
        exit();
        return $response;
    }

}
