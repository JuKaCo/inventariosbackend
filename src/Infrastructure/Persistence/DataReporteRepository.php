<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Infrastructure\Persistence\DataDatosGeneralesRepository;
use App\Domain\ReporteRepository;
use App\Infrastructure\Persistence\DataEntradaRepository;
use App\Infrastructure\Persistence\DataItemRepository;
use \TCPDF;
use \PDO;
use AbmmHasan\Uuid;

class DataReporteRepository implements ReporteRepository {

    /**
     * @var $db conection db
     */
    private $db;
    private $datos;
    private $dataEntradaRepository;
    private $dataItemRepository;

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

    }

    public function reporteIngresoNotaIngreso($id_entrada): array {
        $error = false;
        try {
            $entrada = $this->dataEntradaRepository->getEntrada($id_entrada);
            if ($entrada['success'] == true) {
                $datosEntrada = $entrada['data_entrada'];
                $quey = array('filtro'=>$id_entrada, 'limite'=>'100', 'indice'=>'0');
                $item = $this->dataItemRepository->listItem($quey, $id_entrada);
                if ($item['success'] == true) {
                    $datosItem = $item['data_item']['resultados'];
                    /* Inicio PDF */
                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    $pdf->SetCreator(PDF_CREATOR);
                    $pdf->SetAuthor('CEASS');
                    $pdf->SetTitle('Nota de ingreso almacen');
                    $pdf->SetSubject('Nota de ingreso almacen');
                    $pdf->SetKeywords('Almacen, Ingreso, CEASS');

                    
                    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

                    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


                    $pdf->setFontSubsetting(true);
                    $pdf->SetFont('dejavusans', '', 14, '', true);
                    $pdf->AddPage();

                    //$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
                    $fuenteFin = isset($datosEntrada->tipo_financiamiento->valor)?$datosEntrada->tipo_financiamiento->valor:'Sin datos';


                    
                    $filas = "";
                    $i = 0;
                    $total = 0;
                    $fecha = date("d/m/Y");
                    foreach ($datosItem as $item) {
                        // para tabla de valores
                        $espec_tecn = isset($item->id_producto->especificacion_tec)?$item->id_producto->especificacion_tec:'';
                        $costoTotal = intval($item['cantidad'])*intval($item['costo_almacen']);
                        $i++;
                        $total += $costoTotal;
                        $filas .= "
                            <tr>
                                <td>$i</td>
                                <td>{$item['id_producto']['codigo']}</td>
                                <td>{$item['id_producto']['nombre_comercial']}</td>
                                <td>{$item['id_producto']['form_farm']}</td>
                                <td>$espec_tecn</td>
                                <td>{$item['lote']}</td>
                                <td>{$item['fecha_exp']}</td>
                                <td>{$item['cantidad']}</td>
                                <td>{$item['costo_almacen']}</td>
                                <td>$costoTotal</td>
                                <td>{$item['costo_neto']}</td>
                                <td>{$item['precio_venta']}</td>
                            </tr>
                        ";
                    }
                    $html = <<<EOD
                            <!DOCTYPE html>
                            <html lang="es">
                                <head>
                                    <meta charset="UTF-8">
                                    <title>Document</title>
                                    <style>
                                        .titulo { 
                                            text-align: center; 
                                            font-weight:bold; 
                                            font-size: 15px;
                                        }
                                        .tabla {
                                            font-size: 12px;
                                            aling-self: center;
                                        }
                                        .datatable{
                                            border: 1px solid #000000;
                                            font-size: 10px;
                                            margin: 15px;
                                            text-align: center;
                                        }
                                        .datatable > td{
                                            border: 1px solid #000000;
                                            border-collapse: collapse;
                                        }
                                        .datatable > th{
                                            border: 1px solid #000000;
                                            border-collapse: collapse;
                                            font-weight:bold; 
                                        }
                                    </style>
                                </head>
                                <body> 
                                    <div class="container">
                                        
                                        <p class="titulo"> NOTA DE INGRESO ALMACEN {$datosEntrada['codigo']} </p>
                                        <div class="tabla">
                                            <table>
                                                <tr>
                                                    <td style="font-weight:bold;">Regional:</td>
                                                    <td>{$datosEntrada['id_regional']['nombre']}</td>
                                                    <td style="font-weight:bold;">A favor de:</td>
                                                    <td>CEASS</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold;">Almacen:</td>
                                                    <td>{$datosEntrada['id_almacen']['nombre']}</td>
                                                    <td style="font-weight:bold;">Tipo Ingreso:</td>
                                                    <td>{$datosEntrada['tipo_entrada']['valor']}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold;">Fuente Fin:</td>
                                                    <td>{$fuenteFin}</td>
                                                    <td style="font-weight:bold;">Nro. Factura:</td>
                                                    <td>{$datosEntrada['factura_comercial']}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold;">Proveedor:</td>
                                                    <td>{$datosEntrada['id_proveedor']['nombre']}</td>
                                                    <td style="font-weight:bold;">CITE Contrato u Orden Compra:</td>
                                                    <td>{$datosEntrada['cite_contrato_compra']}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold;">C - 31:</td>
                                                    <td>{$datosEntrada['c_31']}</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold;">Proceso:</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div>
                                            <table class="datatable">
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Codigo</th>
                                                    <th>Nombre</th>
                                                    <th>Forma Farmaceutica o Presentacion</th>
                                                    <th>Especificación Técnica o Concentración</th>
                                                    <th>Lote ó Modelo</th>
                                                    <th>Fecha Expiracion</th>
                                                    <th>Cantidad [u]</th>
                                                    <th>Costo unitario Almacenes (Bs)</th>
                                                    <th>Costo total Almacenes (Bs)</th>
                                                    <th>Precio unitario neto (Bs)</th>
                                                    <th>Precio venta (Bs)</th>
                                                </tr>
                                                $filas

                                                <tr>
                                                    <td colspan="8">
                                                        SEISCIENTOS CUARENTA Y SIETE MIL QUINIENTOS OCHO 00/100 BOLIVIANOS
                                                    </td>
                                                    <td>
                                                        Total
                                                    </td>
                                                    <td>
                                                        $total
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </table>
                                            <small>Fecha: $fecha</small>
                                            <br>

                                            <small><b>Observaciones</b></small>
                                        <br>
                                            <table>
                                                <tr>
                                                    <td><small>Recepcionado Por:</small></td>
                                                    <td><small>Entregado Por:</small></td>
                                                    <td><small>Supervisado Por:</small></td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>
                                </body>
                            </html>
                            EOD;

                    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

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
                return array('sucess' => false);
            }
        } catch (Exception $e) {
            return array('error' => true);
        }
        return array();
    }
}
