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

                    // $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

                    // $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                    $pdf->SetMargins(8, 0, 8);

                    $pdf->SetHeaderMargin(0);
                    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


                    $pdf->setFontSubsetting(true);
                    $pdf->SetFont('dejavusans', '', 14, '', true);
                    $pdf->AddPage();
                    //$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
                    $fuenteFin = isset($datosEntrada->tipo_financiamiento->valor) ? $datosEntrada->tipo_financiamiento->valor : 'Sin datos';

                    $filas = "";
                    $i = 0;
                    $total = 0;
                    $fecha = $datosEntrada['fecha'];
                    $nformater = new NumberFormatter("es-MX", NumberFormatter::SPELLOUT);

                    
                    foreach ($datosItem as $item) {
                        // para tabla de valores
                        $espec_tecn = isset($item->id_producto->especificacion_tec) ? $item->id_producto->especificacion_tec : '';
                        $costoTotal = (float)$item['cantidad'] * (float)$item['costo_almacen'];
                        $costoTotal = round($costoTotal, 2);
                        $costoUnit = round($item['costo_almacen'], 2);
                        $costoNeto = round($item['costo_neto'], 2);
                        $precioVenta = round($item['precio_venta'], 2);
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
                                <td>{$costoUnit}</td>
                                <td>$costoTotal</td>
                                <td>{$costoNeto}</td>
                                <td>{$precioVenta}</td>
                            </tr>
                        ";
                    }
                    $total = round($total, 2);
                    $parteEnteraTotal = floor($total);
                    $parteDecimal = round($total - $parteEnteraTotal, 2);
                    $parteDecimalVerificado = $parteDecimal == 0? '00' : substr(strval($parteDecimal), 2);
                    $numeroEnLetras = strtoupper($nformater->format($parteEnteraTotal));
                    $imagenLogo = $this->dataParametricaRepository->getConfiguracion('LOGO_CEASS');
                    $imagenLogo = $imagenLogo[0];
                    $img_base64_encoded = preg_replace('#^data:image/[^;]+;base64,#', '', $imagenLogo['recurso']);
                    $html = <<<EOD
                            <!DOCTYPE html>
                            <html lang="es">
                                <head>
                                    <meta charset="UTF-8">
                                    <title>Document</title>
                                    <style>
                                        .container {
                                            align-items:center;
                                            align-content:center;
                                        }
                                        .titulo { 
                                            text-align: center; 
                                            font-weight:bold; 
                                            font-size: 15px;
                                        }
                                        .tabla {
                                            font-size: 10px;
                                            aling-self: center;
                                            align-items:center;
                                            align-content:center;
                                            width: 100%;
                                        }
                                        .tabla > table {
                                            border-spacing: 5px;
                                        }
                                        .datatable{
                                            border: 1px solid #000000;
                                            font-size: 8px;
                                            margin: 15px;
                                            text-align: center;
                                            border-collapse: collapse;
                                            padding: 5px;
                                            align-self: center;
                                        }
                                        .datatable > td{
                                            border: 1px solid #000000;
                                            border-collapse: collapse;
                                            align-self: center !important;
                                        }
                                        .datatable > th{
                                            border: 1px solid #000000;
                                            border-collapse: collapse;
                                            font-weight:bold;
                                            align-self: center !important;
                                        }
                                        .table3 {
                                            width: 100%;
                                            align-self: center;
                                        }
                                        .table3 > td {
                                          
                                        }
                                    </style>
                                </head>
                                <body> 
                                    <div class="container">
                                        <table style="width: 80¿'%;">
                                            <tr>
                                                <td><img src="@{$img_base64_encoded}" width="180px"></td>
                                                <!--<td style="text-align: rigth;"><img src="@{$img_base64_encoded}" width="180px"></td>-->
                                            </tr>
                                        </table>
                                        
                                        <p class="titulo"> NOTA DE INGRESO ALMACEN {$datosEntrada['codigo']} </p>
                                        <div class="tabla">
                                            <table>
                                                <tr>
                                                    <td style="font-weight:bold; width: 120px;">Regional:</td>
                                                    <td>{$datosEntrada['id_regional']['nombre']}</td>
                                                    <td style="font-weight:bold;  width: 120px;">A favor de:</td>
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
                                                    <td style="font-weight:bold;">CITE Contrato:</td>
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
                                        <div style="with=100%">
                                            <table class="datatable">
                                                <tr>
                                                    <th style="width: 4%;">No</th>
                                                    <th>Codigo</th>
                                                    <th>Nombre</th>
                                                    <th style="width: 11%">Forma <br> Farmaceutica <br> o Presentacion</th>
                                                    <th style="width: 10.6%">Especificación <br>Técnica o <br>Concentración</th>
                                                    <th>Lote ó <br>Modelo</th>
                                                    <th>Fecha <br>Expiracion</th>
                                                    <th>Cantidad <br>[u]</th>
                                                    <th>Costo<br> unitario<br> Almacenes (Bs)</th>
                                                    <th>Costo total<br> Almacenes<br> (Bs)</th>
                                                    <th>Precio<br> unitario<br> neto <br>(Bs)</th>
                                                    <th>Precio <br>venta <br>(Bs)</th>
                                                </tr>
                                                $filas

                                                <tr>
                                                    <td colspan="8" style="font-weight:bold; font-size: 9px; text-align: left; ">
                                                        $numeroEnLetras $parteDecimalVerificado/100 BOLIVIANOS
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
                                            <small style="font-size: 9px;">Fecha: $fecha</small>
                                            <br>

                                            <small style="font-size: 10px; font-weight:bold;">Observaciones</small>
                                            <br>
                                            <table class="table3">
                                                <tr style="width:100%">
                                                    <td style="border: 1px solid #000000; width: 32%">
                                                            <small style="margin-top: 0px; padding-top:0px; font-weight:bold; font-size: 9px;">Recepcionado Por:</small>
                                                            <br>
                                                            <small style="margin-botton:0px; padding-botton:0px;">
                                                            .......................................................
                                                            </small>
                                                    </td>
                                                    <td  style="width: 1%"></td>
                                                    <td style="border: 1px solid #000000; width: 32%">
                                                            <small style="margin-top: 0px; padding-top:0px; font-weight:bold; font-size: 9px;">Entregado Por:</small>
                                                            <br>
                                                            <small style="margin-botton:0px; padding-botton:0px;">
                                                            .......................................................
                                                            </small>
                                                    </td>
                                                    <td  style="width: 1%"></td>
                                                    <td style="border: 1px solid #000000; width: 32% ">
                                                            <small style="margin-top: 0px; padding-top:0px; font-weight:bold; font-size: 9px; ">
                                                                Supervisado Por:
                                                            </small>
                                                            <br>
                                                            <small style="margin-botton:0px; padding-botton:0px;">
                                                            .......................................................
                                                            </small>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>
                                </body>
                            </html>
                            EOD;


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

                    $filas = "";
                    $i = 0;
                    $total = 0;
                    $fecha = $datosEntrada['fecha'];
                    $nformater = new NumberFormatter("es-MX", NumberFormatter::SPELLOUT);
                    // nuevooooooooooooooooooasdsa dsadsasad///////////////////////////////////////////////////////////////////
                    foreach ($datosItem as $item) {
                        // para tabla de valores
                        $espec_tecn = isset($item->id_producto->especificacion_tec) ? $item->id_producto->especificacion_tec : '';
                        $importe = (float)$item['cantidad'] * (float)$item['costo_almacen'];
                        $importe = round($importe, 2);
                        $i++;
                        $precioUnit = round($item['costo_almacen'], 2);
                        $total += $importe;
                        $filas .= "
                            <tr>
                                <td>$i</td>
                                <td>{$item['id_producto']['codigo']}</td>
                                <td>{$item['id_producto']['nombre_comercial']}</td>
                                <td>{$item['id_producto']['form_farm']}</td>
                                <td>$espec_tecn</td>
                                <td>{$item['lote']}</td>
                                <td>{$item['fecha_exp']}</td>
                                <td>{$precioUnit}</td>
                                <td>{$item['cantidad']}</td>
                                <td>$importe</td>
                            </tr>";
                    }

                    $parteEnteraTotal = floor($total);
                    $parteDecimal = round($total - $parteEnteraTotal, 2);
                    $parteDecimalVerificado = $parteDecimal == 0? '00' : substr(strval($parteDecimal), 2);
                    $numeroEnLetras = strtoupper($nformater->format($parteEnteraTotal));

                    $imagenLogo = $this->dataParametricaRepository->getConfiguracion('LOGO_CEASS');
                    $imagenLogo = $imagenLogo[0];
                    $img_base64_encoded = preg_replace('#^data:image/[^;]+;base64,#', '', $imagenLogo['recurso']);

                    $anio = date("Y");

                    $codigoEntrada = $datosEntrada['codigo'];
                    $arrayCodigo = explode('-', $codigoEntrada);
                    $secuencial = $arrayCodigo[3]; 
                    /* Datos usuario que imprime */
                    $nombre=$token->name;
                    $cargo_usuario=$token->cargo_usuario;


                    $comision = "";
                    $dataComision = $datosEntrada['comision'];
                    for ($i=0; $i < count($dataComision); $i++) {
                        //datos
                        $cargo = ($dataComision[$i])->cargo;
                        $nombres = ($dataComision[$i])->nombres;
                        $apellidos = ($dataComision[$i])->apellidos;
                        //class 
                        $class_td_comision = 'class="td_comision"';
                        $class_titulo_comision = 'class="titulo_comision"';
                        $class_comision = 'class="comision"';
            
                        if ($i == 1 && count($dataComision) > 2) {
                            $comision.= "
                                <td ${class_td_comision}>
                                    <small ${class_titulo_comision}>Comisión de Recepción:</small>
                                    <br>
                                    <br>
                                    <span ${class_comision}>
                                        {$cargo} - {$nombres} {$apellidos}
                                    </span>
                                </td> </tr><tr>";
                        } elseif ($i == 1) {
                            $comision.= "
                            <td ${class_td_comision}>
                                <small ${class_titulo_comision}>Comisión de Recepción:</small>
                                <br>
                                <br>
                                <span ${class_comision}>
                                    {$cargo} - {$nombres} {$apellidos}
                                </span>
                            </td> </tr>";
                        } elseif ($i % 4 == 0 && count($dataComision) > $i + 1 && $i != 0) {
                            $comision.= "
                                <td ${class_td_comision}>
                                    <small ${class_titulo_comision}>Comisión de Recepción:</small>
                                    <br>
                                    <br>
                                    <span ${class_comision}>
                                        {$cargo} - {$nombres} {$apellidos}
                                    </span>
                                </td> </tr><tr>";
                        } elseif ($i % 4 == 0 && $i != 0) {
                            $comision.= "
                            <td ${class_td_comision}>
                                <small ${class_titulo_comision}>Comisión de Recepción:</small>
                                <br>
                                <br>
                                <span ${class_comision}>
                                    {$cargo} - {$nombres} {$apellidos}
                                </span>
                            </td> </tr>";
                        } elseif (count($dataComision) == $i + 1) {
                            $comision.= "
                            <td ${class_td_comision}>
                                <small ${class_titulo_comision}>Comisión de Recepción:</small>
                                <br>
                                <br>
                                <span ${class_comision}>
                                    {$cargo} - {$nombres} {$apellidos}
                                </span>
                            </td> </tr>";
                        } else {
                            $comision.= "
                                    <td ${class_td_comision}>
                                        <small ${class_titulo_comision}>Comisión de Recepción:</small>
                                        <br>
                                        <br>
                                        <span ${class_comision}>
                                            {$cargo} - {$nombres} {$apellidos}
                                        </span>
                                    </td>";
                        }
                    }

                    $html = <<<EOD
                                <!DOCTYPE html>
                                <html lang="es">
                                    <head>
                                        <meta charset="UTF-8">
                                        <title>Document</title>
                                        <style>
                                            .container {
                                                align-items:center;
                                                align-content:center;
                                            }
                                            .titulo { 
                                                text-align: center; 
                                                font-weight:bold; 
                                                font-size: 15px;
                                            }
                                            .tabla {
                                                font-size: 10px;
                                                aling-self: center;
                                                align-items:center;
                                                align-content:center;
                                                width: 100%;
                                            }
                                            .tabla > table {
                                                border-spacing: 5px;
                                            }
                                            .datatable{
                                                border: 1px solid #000000;
                                                font-size: 8px;
                                                margin: 15px;
                                                text-align: center;
                                                border-collapse: collapse;
                                                padding: 5px;
                                                align-self: center;
                                            }
                                            .datatable > td{
                                                border: 1px solid #000000;
                                                border-collapse: collapse;
                                                align-self: center !important;
                                            }
                                            .datatable > th{
                                                border: 1px solid #000000;
                                                border-collapse: collapse;
                                                font-weight:bold;
                                                align-self: center !important;
                                            }
                                            .table3 {
                                                width: 100%;
                                                align-self: center;
                                            }
                                            .table3 > td {
                                            
                                            }
                                            .mensaje {
                                                font-size: 10px;
                                                width: 50% !important;
                                            }
                                            .comision {
                                                font-size: 8px;
                                                text-align: center !important;
                                                padding-bottom:0px;
                                                margin-bottom:0;
                                            }
                                            .td_comision {
                                                border: 1px solid #000000;
                                                width: 32%;
                                                padding:0px; 
                                                margin:0px
                                            }
                                            .titulo_comision {
                                                margin-top: 0px;
                                                padding-top:0px;
                                                font-weight:bold;
                                                font-size: 9px;
                                            }
                                        </style>
                                    </head>
                                    <body> 
                                        <div class="container">
                                            <table style="width: 80%;">
                                                <tr>
                                                    <td><img src="@{$img_base64_encoded}" width="180px"></td>
                                                    <!--<td style="text-align: rigth;"><img src="@{$img_base64_encoded}" width="180px"></td>-->
                                                </tr>
                                            </table>
                                            
                                            <p class="titulo"> ACTA DE RECEPCIÓN $secuencial/$anio - {$datosEntrada['codigo']} </p>

                                            <p class="mensaje">En la ciudad de El Alto, en las instalaciones de la <span style="font-weight:bold;">
                                                Central de Abastecimiento y Suministros de Salud CEASS</span>, se procede a la recepción detallado a continuación: </p>

                                            <div class="tabla">
                                                <table>
                                                    <tr>
                                                        <td style="font-weight:bold; width: 130px;">Proceso:</td>
                                                        <td colspan="2">{$datosEntrada['id_compra']['nombre']}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="font-weight:bold;">Modalidad:</td>
                                                        <td>{$datosEntrada['modalidad_contratacion']['valor']}</td>
                                                        <td style="font-weight:bold; width: 130px;">CITE Contrato:</td>
                                                        <td>{$datosEntrada['cite_contrato_compra']}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="font-weight:bold;">Proveedor:</td>
                                                        <td>{$datosEntrada['id_proveedor']['nombre']}</td>
                                                        <td style="font-weight:bold;">Nro. Factura:</td>
                                                        <td>{$datosEntrada['factura_comercial']}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="font-weight:bold;">C - 31:</td>
                                                        <td>{$datosEntrada['c_31']}</td>
                                                        <td style="font-weight:bold;">Estado:</td>
                                                        <td>{$datosEntrada['estado']}</td>
                                                    </tr>

                                                </table>
                                            </div>

                                            <div style="with=100%">
                                                <table class="datatable">
                                                    <tr>
                                                        <th style="width: 4%;">No</th>
                                                        <th>Codigo</th>
                                                        <th>Nombre</th>
                                                        <th style="width: 11%">Forma <br> Farmaceutica <br> o Presentacion</th>
                                                        <th style="width: 10.6%">Especificación <br>Técnica o <br>Concentración</th>
                                                        <th>Lote ó <br>Modelo</th>
                                                        <th>Fecha <br>Expiracion</th>
                                                        <th>Precio<br> unitario<br>(Bs)</th>
                                                        <th>Cantidad <br>[u]</th>
                                                        <th>Importe <br>Total<br> (Bs)</th>
                                                    </tr>
                                                    $filas
                                                    <tr>
                                                        <td colspan="9" style="font-weight:bold; font-size: 9px; text-align: left; ">
                                                            $numeroEnLetras $parteDecimalVerificado/100 BOLIVIANOS
                                                        </td>
                                                        <td>
                                                            $total
                                                        </td>
                                                    </tr>
                                                </table>
                                                <p class="mensaje">Como constancia de la recepción de lo anteriormente citado, se firma la 
                                                          presente Acta de Recepción, dando conformidad a la entrega que se realizo de acuerdo a las 
                                                          especificaciones técnicas.
                                                </p> 
                                                <br>
                                                <small style="font-size: 9px;">Fecha: $fecha</small>
                                                    <br>
                                                <small style="font-size: 10px; font-weight:bold;">Observaciones:</small>
                                                    <br>

                                                <table class="table3">
                                                    <tr style="width:100%">
                                                        <td style="border: 1px solid #000000; width: 32%; padding:0px; margin:0px"  >
                                                                <small style="margin-top: 0px; padding-top:0px; font-weight:bold; font-size: 9px;">Recepcionado Por:</small>
                                                                <br>
                                                                <br>
                                                                <span style="font-size: 8px; text-align: center; padding-bottom:0px; margin-bottom:0;">
                                                                    $cargo_usuario - $nombre
                                                                </span>
                                                        </td>

                                                        $comision
                                                        
                                                </table>
                                            </div>
                                    </body>
                                </html>
                            EOD;
                    
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
