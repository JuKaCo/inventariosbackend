<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Infrastructure\Persistence\DataDatosGeneralesRepository;
use App\Domain\ReporteRepository;
use App\Infrastructure\Persistence\DataEntradaRepository;
use App\Infrastructure\Persistence\DataItemRepository;
use App\Infrastructure\Persistence\DataParametricaRepository;
use App\Infrastructure\Persistence\DataCotizacionRepository;
use App\Infrastructure\Persistence\DataItemSecRepository;
use App\Infrastructure\Persistence\MYPDF\MYPDF;
use App\Infrastructure\Persistence\DataTemplateRepository;
use \TCPDF;
use \PDO;
use AbmmHasan\Uuid;
use Exception;
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
    private $dataCotizacionRepository;
    private $dataItemSecRepository;
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
        $this->dataCotizacionRepository = new DataCotizacionRepository;
        $this->dataItemSecRepository = new DataItemSecRepository;
        $this->template = new DataTemplateRepository;
    }


    public function reporteIngresoNotaIngreso($id_entrada, $token): array {
        //$nformater = new NumberFormatter("es-MX", NumberFormatter::SPELLOUT);
        //$numeroEnLetras = $nformater->format(2021);
        //print $numeroEnLetras;
        if(!($this->verificaPermisos($id_entrada,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
        $error = false;
        try {
            $entrada = $this->dataEntradaRepository->getEntrada($id_entrada, $token);
            if ($entrada['success'] == true) {
                $datosEntrada = $entrada['data_entrada'];
                $quey = array('filtro' => $id_entrada, 'limite' => '1000', 'indice' => '0');
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
        if(!($this->verificaPermisos($id_entrada,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
        $error = false;
        try {
            $entrada = $this->dataEntradaRepository->getEntrada($id_entrada, $token);
            if ($entrada['success'] == true) {
                $datosEntrada = $entrada['data_entrada'];
                $quey = array('filtro' => $id_entrada, 'limite' => '1000', 'indice' => '0');
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

    // reporte cotizacion
    public function reporteCotizacion($id_cotizacion, $token): array {
        if(!($this->verificaPermisos($id_cotizacion,null,$token))){
            return array('success'=>false,'message'=>'usuario no autorizado','code'=>403,'data_entrada'=>array());
        }
        $error = false;
        try {
            $cotizacion = $this->dataCotizacionRepository->getCotizacion($id_cotizacion, $token);
            if ($cotizacion['success'] == true) {
                $datosCotizacion = $cotizacion['data_cotizacion'];
                $quey = array('filtro' => "", 'limite' => '1000', 'indice' => '0');
                $id_cotizacion = 'xxxx-xxxx-xxxx-xxxxxxxx';
                // $id_cotizacion = $datosCotizacion

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





    private function verificaPermisos($uuid_registro_a_modificar,$id_regional_registro_nuevo,$token){
        //sacamos los datos del token
        $regional_usuario=$token->regional;
        $privilegio_usuario=$token->privilegio;
        if($privilegio_usuario=='total'){//el usuario tiene acceso total
            return true;
        }else{//el usuario tiene acceso limitado a su regional
            if($uuid_registro_a_modificar==null){
                //es una alta
                if($id_regional_registro_nuevo!=$regional_usuario){
                    //el nuevo registro que intenta introducir el usuario pertenecerá a otra regional
                    return false;
                }else{
                    return true;//el nuevo registro pertenece a la regional del usuario
                }
            }else{
                //es una modificacion
                $sql = "SELECT id_regional
                        FROM entrada
                        WHERE id=:uuid;";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':uuid', $uuid_registro_a_modificar, PDO::PARAM_STR);
                $res->execute();
                if($res->rowCount()>0){
                    $res = $res->fetchAll(PDO::FETCH_ASSOC);
                    $id_regional_ant = $res[0]['id_regional'];
                    if($id_regional_ant!=$regional_usuario){
                        //el usuario intenta modificar un registro distinto al de su regional
                        return false;
                    }else{
                        if($id_regional_registro_nuevo==null){
                            return true;
                        }else{
                            if($id_regional_registro_nuevo!=$regional_usuario){
                                //el nuevo registro que intenta modificar el usuario pertenecerá a otra regional
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }
                }else{
                    return false;
                }                    
            }
        }
    }
}
