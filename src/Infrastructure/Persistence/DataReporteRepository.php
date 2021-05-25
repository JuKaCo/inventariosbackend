<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Infrastructure\Persistence\DataDatosGeneralesRepository;
use App\Domain\ReporteRepository;
use App\Infrastructure\Persistence\DataEntradaRepository;
use App\Infrastructure\Persistence\DataItemRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\MYPDF\MYPDF;
use App\Infrastructure\Persistence\DataTemplateRepository;
use \TCPDF;
use \PDO;
use AbmmHasan\Uuid;
use \NumberFormatter;

class DataReporteRepository implements ReporteRepository {

    /**
     * @var $db conection db
     */
    private $db;
    private $datos;
    private $dataEntradaRepository;
    private $dataItemRepository;
    private $dataParametricaRepository;
    private $template;

    /**
     * DataMenuRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->datos = new DataDatosGeneralesRepository;
        $this->dataEntradaRepository = new DataEntradaRepository;
        $this->dataItemRepository = new DataItemRepository;
        $this->dataParametricaRepository = new DataParametricaRepository;
        $this->template = new DataTemplateRepository;
    }


    public function reporteIngresoNotaIngreso($id_entrada): array {
        //$nformater = new NumberFormatter("es-MX", NumberFormatter::SPELLOUT);
        //$numeroEnLetras = $nformater->format(2021);
        //print $numeroEnLetras;
        $error = false;
        try {
            $entrada = $this->dataEntradaRepository->getEntrada($id_entrada);
            if ($entrada['success'] == true) {
                $datosEntrada = $entrada['data_entrada'];
                $quey = array('filtro' => $id_entrada, 'limite' => '100', 'indice' => '0');
                $item = $this->dataItemRepository->listItem($quey, $id_entrada);
                if ($item['success'] == true) {
                    $datosItem = $item['data_item']['resultados'];
                    /* Inicio PDF */
                    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, 'mm', array('279', '216'), true, 'UTF-8', false);
                    $pdf->datos($datosEntrada['id']);
                    
                    $pdf->SetCreator('');
                    $pdf->SetAuthor('CEASS');
                    $pdf->SetTitle('Nota de ingreso almacen');
                    $pdf->SetSubject('Nota de ingreso almacen');
                    $pdf->SetKeywords('Almacen, Ingreso, CEASS');

                    $pdf->setPrintHeader(false);

                    $pdf->SetMargins(8, 0, 8);

                    $pdf->SetHeaderMargin(0);
                    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


                    $pdf->setFontSubsetting(true);
                    $pdf->SetFont('dejavusans', '', 14, '', true);
                    $pdf->AddPage();

                    $html = $this->template->getNotaIngresoTemplate($datosEntrada, $datosItem);

                    $pdf->writeHTML($html, true, 0, true, 0);
                    /* Creado QR */
                    $uri_valid = $_ENV['VALID_URL'];
                    $pdf->write2DBarcode($uri_valid.'nota_ingreso/'.$datosEntrada['id'], 'QRCODE,L', 170, 5, 0, 25, array(), 'N');
                    $pdf->Output();
                    /* Fin PDF */
                    //return $datosItem;
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
            if ($error == true) {
                return array('sin_datos' => true);
            }
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }

    public function reporteIngresoActaRecepcion($id_entrada, $token): array {
        $error = false;
        try {
            $entrada = $this->dataEntradaRepository->getEntrada($id_entrada);
            if ($entrada['success'] == true) {
                $datosEntrada = $entrada['data_entrada'];
                $quey = array('filtro' => $id_entrada, 'limite' => '100', 'indice' => '0');
                $item = $this->dataItemRepository->listItem($quey, $id_entrada);
                if ($item['success'] == true) {
                    $datosItem = $item['data_item']['resultados'];
                    /* *PDF aqui* */
                    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, 'mm', array('279', '216'), true, 'UTF-8', false);
                    $pdf->datos($datosEntrada['id']);
                    
                    $pdf->SetCreator('');
                    $pdf->SetAuthor('CEASS');
                    $pdf->SetTitle('Nota de ingreso almacen');
                    $pdf->SetSubject('Nota de ingreso almacen');
                    $pdf->SetKeywords('Almacen, Ingreso, CEASS');
                    $pdf->setPrintHeader(false);
                    $pdf->SetMargins(8, 0, 8);

                    $pdf->SetHeaderMargin(0);
                    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


                    $pdf->setFontSubsetting(true);
                    $pdf->SetFont('dejavusans', '', 14, '', true);
                    $pdf->AddPage();

                    $html = $this->template->getActaRecepcionTemplate($datosEntrada, $datosItem, $token);
                    $pdf->writeHTML($html, true, 0, true, 0);
                    /* Creado QR */
                    $uri_valid = $_ENV['VALID_URL'];
                    $pdf->write2DBarcode($uri_valid.'nota_ingreso/'.$datosEntrada['id'], 'QRCODE,L', 170, 5, 0, 25, array(), 'N');
                    $pdf->Output();

                    /* *PDF Finaliza* */
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
            if ($error == true) {
                return array('sin_datos' => true);
            }
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }
}
