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
use \NumberFormatter;

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
                    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, 'mm', array('279', '216'), true, 'UTF-8', false);
                    
                    $pdf->SetCreator('');
                    $pdf->SetAuthor('CEASS');
                    $pdf->SetTitle('Nota de ingreso almacen');
                    $pdf->SetSubject('Nota de ingreso almacen');
                    $pdf->SetKeywords('Almacen, Ingreso, CEASS');

                    $pdf->setPrintHeader(false);

                    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

                    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                    $pdf->SetMargins(8, -5, 8);

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
                    $fecha = date("d/m/Y");
                    $nformater = new NumberFormatter("es-MX", NumberFormatter::SPELLOUT);
                    foreach ($datosItem as $item) {
                        // para tabla de valores
                        $espec_tecn = isset($item->id_producto->especificacion_tec) ? $item->id_producto->especificacion_tec : '';
                        $costoTotal = intval($item['cantidad']) * intval($item['costo_almacen']);
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
                    $parteEnteraTotal = floor($total);
                    $parteDecimal = round($total - $parteEnteraTotal, 2);
                    $parteDecimalVerificado = $parteDecimal == 0? '00' : substr(strval($parteDecimal), 2);
                    $numeroEnLetras = strtoupper($nformater->format($parteEnteraTotal));
                    $img_base64_encoded = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAEAAAAAAAD//gAfTEVBRCBUZWNobm9sb2dpZXMgSW5jLiBWMS4wMQD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCACzAUwDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKK8r/bN/aq0n9jX4A6t441W1k1KS1ZLXT9PjlET6jdyHEUO8g7F6s77WKxo7BXICn8gfiL/AMFc/wBoL4i6/LeL48bw7bu5aLT9F0+3t7a37YDOjzMP+ukje2K8HNuIcLl8lTq3cnrZdvO7R+ncC+E+dcU0Z4rBuFOlF8vNNtJu12oqKk3a6u2ktd27o/dKivwN/wCHmP7QH/RWPFH/AHzb/wDxqj/h5j+0B/0VjxR/3zb/APxqvH/15wn/AD7l+H+Z+gf8S057/wBBVH/yp/8AIH75UV+Bv/DzH9oD/orHij/vm3/+NUf8PMf2gP8AorHij/vm3/8AjVH+vOE/59y/D/MP+Jac9/6CqP8A5U/+QP3yor8FNN/4KhftCaTepcQ/FbxA0kfQT2tncRn6pJAyn8R+Vfp9/wAEr/8AgoXcftwfD7WLHxJa2Vj448JNEL8WgKW2oW8u/wAq5jRiShzG6OmWCsqtkCRUX0sr4owmNrewgnGT2vbX0s3qfH8aeC+ecOYB5nWnCrSi0pODleN3ZNqUY6NtK6b1eqS1Pq2iiivpD8hCiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigD4L/AODhRv8AjEvwOO3/AAnMB/8AKbqNfkfX64f8HCn/ACab4H/7HiD/ANNuo1+R9fkfGH/Iyl6L8j+9PAL/AJJCl/jqf+lBRToIJLqeOKKOSWWVgiIilmdicAADkkngAda0f+EI13/oA65/4L5v/ia+ZUW9j9llUhH4mkZlFaf/AAhGu/8AQB1z/wAF83/xNU9Q0u60i4EN5a3VnMVDiO4haJypyAcMAccHn2NDjJboUasJO0Wn8yCvuL/ggBcSJ+2l4khVmEUngq7dlB4ZlvrAKT9Nzfma+Ha+3v8AggF/ye14g/7Ee9/9L9Or1uH/APkY0fU+D8VNeEcff/n2/wA0fsNRRRX7Yf5xhRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFcd8R/2gPBvwknWHxB4gsdPumUOLbLTXG09G8qMM+04POMcVyX/Dd/wq/wChok/8FV7/APGa8PF8UZNharoYrF0oTW6lUgmvVNpnq4fIsyxEFVoYepKL2ahJr70rHr1FeQ/8N3/Cr/oaJP8AwVXv/wAZo/4bv+FX/Q0Sf+Cq9/8AjNcv+unD3/QfR/8ABsP/AJI3/wBWc4/6BKv/AILn/kfNP/Bwp/yab4H/AOx4g/8ATbqNfkfX6of8Fj/ifov7Vv7PHhXQ/h/dSeIdW03xXFqVzb+Q9n5duLG9iMm64EaHDyxjaGLfNnGASPzr/wCGYfiBj/kWpP8AwYWf/wAer8w4o4iymtj3OjiqUlZaqpBrb1P7U8EakMBwtTw+OkqU1Ob5ZtRlZy00lZ69NCb9kL/k734R/wDY9aD/AOnK3r+iKv57f2aPCup+DP20fhHZatp93p11/wAJxoLCOeMrvX+07cblPRlzxlSRkda/oSr7fgKpGeHqTg7ptWa1Wx+U/SWnGeOwMou6cJar/Egr8b/+C+LE/t3aX/s+B9PA9v8ATtSr9kK/G7/gvh/yffpn/Ykad/6W6jXpcZf8i7/t5fqfL/R7/wCSsX/Xqf8A7afFlfb3/BAL/k9rxB/2I97/AOl+nV8Q19sf8EEtSt9J/bR8QzXVxDaw/wDCE3i+ZK4Rcm/07AyeO1fnmR1YU8fSnUaST1b0R/VnilFvhPHpf8+3+aP2MorJ/wCE70P/AKDGl/8AgWn+NH/Cd6H/ANBjS/8AwLT/ABr9d/tjAf8AP+H/AIFH/M/zo+q1v5H9zNaisn/hO9D/AOgxpf8A4Fp/jVrTfEFhrLMtnfWl0y8sIZlkx9cGtKWZ4OrJQpVYyb6KSb+5MmVCpFXlFpejLlFFfLf/AAVg/wCCnWh/8Ew/gDa+IrjS18SeLPEl2dP8PaJ9pFut1Kq75Z5XwzLBEpBYqrEtJEny+ZvX2MDgq2Lrxw2HjzTk7Jf1+fQ5MRiKdCm6tV2itWz6kor+Z/4pf8HD37Wnxd8RMun+PtP8Ix3x2RaV4a8PWiqT6RvcJPc5+ktczqX/AAUk/bW0y2kv7z4ifGi1t4x5jzS2EkUKAdyTCFA/Sv0Sn4V5lZe1rU4t9Lv/AOR/K58tLjLCf8u4Sku9v+Cf1DUV/Nr+zR/wcg/tLfBXxTp914k8Saf8VfDMbILnStZsLa3mmhDZYQ3ltEkiSkZAkl85VOCUbpX67f8ABUb9t7VtB/4I0658dPg74iutDvtWsPD2raDqgtoZJ7eC+1TT0YNFKskYcwzvGyspKksOCMjws04HzHAYqjhqzi/bSUYyTfLdtLXRNb329L2PTwPEGFxdKdSnf3FdprX+tD7Uor+Xy2/4LpfthX0hSD40eIJ3UbisegaS5A6ZwLP3FT/8Pvv2zP8Aor3ir/wmtL/+Q69//iE+ada1P75f/InlrjTBPaMvuX+Z/T5RX82P7Pf/AAWd/a88X/tBeAdI1b4seJrnSdW8TaZY3sL+HtMRZoJbyKORCwtAQGRmGQQRngg1/SdXynEnC+JyWcIYmcZc6bXK29rb3S7nt5Xm9HHxlKkmuXvoFFfnV/wcV/8ABRnxh+wx8CvAek/DXxFJ4Y8eeONZlkW+js4Ll4dNtIgbjaJkdAzTT2i5KnKmTGCMj8jH/wCC7X7XjR/8lu1xQ2QGGh6R19v9E7ZFerkfh/mGaYSONozhGMm0uZyu7O19IvS9+vQ4sy4mwmCruhVTbVtkuvzP6iKK8J/4JnftTN+2f+wn8NfiJcTx3Gra1pCQ6yyRiNf7St2a2vMIOFU3EUpUdNrKRwRXsHjvxxpPwy8D6z4k169h03Q/D9jPqWo3kufLtLaGNpJZGwCcKisxwCcCvjcRhatGvLDTXvRbi15p2/M96nWjOmqsXo1f5GtRX86f7V//AAcw/tBfGDxvfzfDrU9P+FPg9JXXT7aDS7S+1OSDorXU1ykyCU9SIFRUyFBfbvbj9J/az/4KIeNbNdQ028/aa1S1m5S40/wlfSwOD/dMVrs/Kv0Cn4Y5iqaqYmrTp36Sk7+j0t9zZ8zLi7DObhQhKduyP6XKK/me0D/guB+2d+zN43isPEfjfXJLqxZZ59A8aeGbcNKpyAJQ8EV2qEg/clTocGv3N/4JR/8ABQm1/wCClP7JVn49/suLQ9f0+/l0PxBp0LtJBa38SRyExMwBMckU0Mqg5K+bsLMULHx+IOCsflNFYmq4zpt25otu19r3S37q68zuy3iDDY2o6MLxmujVj6Uor8Jv+Cmf/ByV8Un+Pnibwb8C7rSfB/hPwrfy6Wuvy6fBqOpazPBIySzoJxJbx2zOpEa+W7siiQupk8uP5y8Mft1/t/fG/T11rw54g/aC8T2N0vmpd+H/AAtcT2kinoym1tfL2ntt49K9TCeGuY1KEcRXqQpKSTSk3fXvpZel79zjrcV4WNV0aMZTa35Uf0zUV/MtqH/BXD9uL9lvX7e28UeOfiP4durj5lsPGPhaBGulQruAW8tRJjlQxQg/N1Ga/X//AIIf/wDBXC8/4KafDPxJpvjDS9L0b4jeBXgOojTd0djq1pcGTybmGN2Z42VonjlTc6qRG4YCYRx8GecC4/LcL9dco1Kel3Ft2vpd3S0vppc6cv4iw2LrfV0nGfaSsfdVFFFfFHvhXP8AxX8XS/D/AOF/iTXoY0mm0XTLm+jjf7sjRxM4U+xIAroK4j9pf/k3Xx5/2L99/wCk715ucVp0cBXrU3aUYSafZqLaOzLacamLpU5q6cop+jaPzY1HUbrWtSuL2+uJry+vJDNcXEp3STyHqzH1P+eKhoor/O+UnJuUnds/seMUlZbBRRmjdUjCijdRuoAm8K+Cbfxp8YfhsZEX7TpPjXQ7+1lI+aJk1G2LgezRhlI6fdPVRj9Xq/L34NnPxp8Ef9jJpf8A6WQ1+oVf1R4A1pvLcTTb0U1byvHU/D/F6rJ1sLBvRRlbyu1cK/G7/gvh/wAn36Z/2JGnf+luo1+yNfjd/wAF8Dj9u/TP+xI0/wD9LdRr9Q4y/wCRd/28v1O76Pf/ACVn/cKf/tp8WV9Kf8EtBn4+eIP+xZm/9LLOvmvNfYH/AAS2+FGoWdzr/ji6gkt9NvrQaTpzuCPtoMqyTSL6orRRqGGQWLgHKMB/NviViaVHhvE+1duZKK822rJd3u/RN7I/r7jjEU6WSV/aO3Mkl5ttaL8/RN9D6/2D0H5UbB6D8qWiv405UfzOJsHoPyqS0uJLC7juLeSS3nhbdHLExR4z6hhyD9KZRTi3GSlHRrVPsweqsz61+Bvjub4hfDu0vboq17EzW9yygAO6n72BwNylWwOAWI6V+Hn/AAdiePr7Vf22vhv4XkkZtN0HwQNVtoyThJr2/uYpiB2ythACe+0elfs9+yD/AMkyvP8AsJSf+i4q/Eb/AIOsVx/wUi8JN2b4a6cPy1TV/wDGv9kPo0ZlXzLBZbjcZLmqSoXcnu2o8rk/N7t92z+VfEyjDDwxFKlpFTVl5Xvb0Puv/g1/+A+i+Bf+Cdq+OItNs18QePte1CW51EwJ9qktrab7JHb+Zjd5KvbyuEJ2h5ZGAyxr9Iq+I/8Ag3PlWT/gjv8AClVPzR3Ovq3sf7e1E/yIr7cr2OKq06mc4qU3r7SS+SbS/BDyinGGCpRjtyr8Ufh//wAF1f8Agjr8XPjt+3zdeNPgt8K7jxBoPibQLO81m7sr/T7ONtXEtxFNlJ542LmCO1dmC4ZnJyW3V6R+038G/HH7Pn/BqZfeDPiNo9zoPjDw/Jp9veafPPDPJbR/8JlA1uC8LvGQbdoSNrHAIBwQQP14xXxJ/wAHF3/KHP4tf9fHh/8A9SDTa+gyzirF42rl+WVox5adWk09ebR2V221s+y2R52IyehQWIxlO/NOErrptf8AQ/M7/g1N/wCUj/i7/sm2of8Ap00mv6DK/km/Ye0z456t8XNQj/Z9PjVfGy6NK14fC9wYL3+zvPgEm5gyny/ONvkZ+9s9K+qP+EM/4Kbf89P2k/8AwbS//Ha+y404TWYZk8Q8VTp+6laTs9D5vh3O3hsEqXsZy1eqWm5/RjRX4b/8E5/C37fdl+3L8M5fie/x4Pw/j1fOuDWdRkksPs/ky/65TIQV3beo64r9jv2m/jtp37MP7Ovjf4iasvnWHgvRLvWJIQ4RrowxM6wqTxvkYKijuzgV+W5zkLwOJhhqdWNVzSs4O6u3a3qfaYDMliaUqsoOCX8y/E/nh/4OJf2n1/aJ/wCCmXiiws7pJtD+GNnD4StWjlLRNPFumvHK9FkW5mlgbHUWq+lenf8ABQv/AIJnTfAn/gh/+zl46jsJI/EXh2eS/wDFRWDy3ji1/ZcIbgnnfbSR2VmB0zIfXJ+O/wBh/wCCOpft+/8ABQDwL4T8QTSaxdfELxQb/wATTu/lveW+6S91OQt2d4Y7kg/32HrX9PP7cP7Ntt+19+yJ8RPhrcC2WTxdodxZWUtwpaO0vdu+0nIH/PK4SGQe8Yr9Y4iziOQVMty+m9KdpT81bkb+d5v1PicqwLzOOLxU953Ufz/RH5if8Gm37UAvPDfxO+DN9cL52nzxeMNGjeQs7QyhLW9VR/CkciWbYHVrpz16/oH/AMFe7m6tf+CXnx7azLLI3gnU45Nv/PFoGWX8PLL59s1/PR/wSG/aWuP2Nf8AgpD8NfEWptcaVp9xqv8AwjXiGCb915Ntef6NJ5wPIWCZop2HY234V/UF8XPhhpPxu+FHibwXr0Uk2h+LtJutF1GON9jyW1zC8MqhuxKOwB7V8rx9go5fxBDG29ybjP5xaUl+CfzPa4YxLxOWOg370bx/y/ryP5of+CC1joOo/wDBW74Ox+IIbOa1W7v5bVLtEaH7Ymm3b2zEOMb1lVGjP3hKsRX5gK/qAr+an9pH/g3f/ac/Z78X3ieH/Cq/Ezw/ZTF7HXPDt5Ak0yBiY3ezkkW4jmwFZljEqo3AkfAY8fa6T+3v8MrT7NbW/wC2VpdlZdI7dPEv2SID0C5jA+nFfUcUZHg+Iq8Mbg8ZBJRUbNru3fe6euzXQ8bJcfiMqpSw1fDybu3dL0X3abn7Nf8ABbX/AIJK65/wU/0L4eyeFda8OeHde8F3N4JLrVlm2zWtykW6NfKViSJIIz8wwBux1OdD/gip/wAEyPGP/BMb4W+PNB8VeJPD3iKXxTq0OpWh0oTiKDZAImDiRVO4kL07AV+LWi/8FhP2xv2avEaafqXxP8eaffKqyPpvi3SobqWWPd/Et7A0oUkY3KVPUAiv2l/4Iif8FVb/AP4Kc/BXxJ/wlWlaXpHj/wABXcFvq6aYJFsb63uRI1rdRo5ZoixhnjaMu/zQFgwEgRPm+IMmz3LMk+q1KkKmFTXw7q8rp6pO3M+je56+WZhluMzD20YyjWt166W/I/nU/Yzt9B1X9pb4Pw+MhZzeFbrxboSa+NQx9newa+txdednjyzCZN+e2c1/YOK/ny/4KHf8G4/xq8BfHHxRrnwh8O2Hj34f61qNxqOnWVjfwWuoaLDIxk+zSQXDxiRYixjRonkZ0RSyqxIrwDR/hL+3Z8CbL+x9E0P9rjw7YWahFtdCj8QrZRqowAotcxYAGOOAK+o4kwGC4njRxOExcI8qfutq+tnqrpp9HpqeRlOKxOUSqUa2HlLmd04r+ro/fD/grj+wTd/8FGv2Ob74faTeaPpfiKHVrHV9Iv8AU0drezmil2ysdis4LW0lzGCB1kwcAk184f8ABF//AIIqfEL/AIJoftF+JvF3ijxl4T1/Ste8OPo62mkrcrIs32mCZJG8xFG0LHIvBzl/TNflEf8Agpv+2j+y7qNsusfEj4weG7iVisMXi6wa4MxA+YBNShfdgdeCR14r9Uf+CD//AAWv8U/t8+Mtc+F/xStNJ/4TbSNLOtaVrOnQfZY9YtY5Iop454slVuEaWNwYsLIjP+7j8ktJ87mOQZ7lOS1aNKrCphnrLl1ava71W2i2emrPUwuZ5djsfCc4SjWW19Pl+e6P03ooor8nPswriP2l/wDk3Xx5/wBi/ff+k7129cR+0v8A8m6+PP8AsX77/wBJ3ryc/wD+RXif+vc//SWehlP+/Uf8cfzR+aVFFFf54n9iHsn7Cvw+0X4mfG+703X9NttVsU0O4uVhnXKiRZ7ZQ31Adh/wI19cf8MhfDP/AKE3Rf8Av2f8a+X/APgm5/ycXff9i7df+lNpX3RX9ZeDeS5fiuHlVxNCE5c8leUIt206tNn8++JGZ4yhnDp0KsorljopNLr0TPN/+GQvhn/0Jui/9+z/AI0f8MhfDP8A6E3Rf+/Z/wAa9Ior9W/1Yyb/AKBKX/guH+R8D/bmZf8AQRP/AMDl/mef6V+yt8O9D1W1vrTwjpFveWM8dzbypGd0UkbB0Yc9QwBH0r0CiivQweXYTCRccJSjTT35YqN/WyRyYnGYjENPETlO23M27feFeYfGH9i/4V/tAeLo9e8aeB9C8R6xHapYrd3kRaRYEZ3WPOR8oaRz9WNen0V0VaNOrHlqRUl2auVgswxWDqe2wlSVOW14txdu100zw3Tf+CaPwC0u9juI/hN4LeSM5UTWAmTPur5U/iK9EX4DeDkRVXw9pyrGoVVVMKoAwABngAcADpXXUV5GM4ZyjFtPFYWlO23NTjK3pdM7cRxBmmIadfE1JW2vOT/NnI/8KH8H/wDQv6f/AN8n/GvMf2oPh5ongrw1pc2labbWMk92Y5GiGCy+Wxx+YFe+V47+2R/yKWi/9fzf+inr8n8YuFckwvBuOxGFwdKE4xjaUacE178dmkmj1OGcwxVTM6MJ1JNNvRybWz8z59ooor/Oo/bD6Q/ZB/5Jlef9hKT/ANFxV+O//B2l4BfS/wBqL4SeKTHiPXfC13pSv/eNldiVh+H29fzr9iP2Qv8Akmd5/wBhKT/0XFXg/wDwWx/4Jh3n/BTD9nHSdP8ADN5pWmePvBeoNqOiXGollt7qORPLuLOR1DGNZAI3DhWw9vGDhSxH+tX0a80pZfkmU4iu7Q9nZvtdNJ+idr+R/NPiLgp4qriqVNXle6+VjzH/AINfPinY+M/+Can/AAjsN1G2oeCfE+o2Vzb7x5kSXDLeRvt6hG+0OA3QtG4HKnH6NV/LPdf8E6/2xP2OfHrXWi/DT42+Gde8sw/2l4HS6vJGjODt+1aU8g2ng7S/1GQRWl/wlv7fWpn7It5+2jM68GOMeKPM5552jdz79q/oLOOBaGYY2pjsLjKfLUbluna+r1T11Pg8v4iq4bDww9bDz5oq2i7H3B/wcMf8FPfi1+zB+27ong74T/E/UvDNjaeEbW71mxsI7SdIryW5uiPM8yJ2SUwLCxUkfI8TY+bJ0PjB8bvHX7SH/Bqb4n8b/ETxBeeKPEmuata7tQuo4o5JYYfGNtbopEaquFEJA+UcD8a+Cvgn/wAEYf2qv2qfGu//AIVj4r0N9Uui9/rnjcPpEcTMfmnm+04upeeSY4pXPXB61+wH/BSn9iC9+C3/AAQF1j4IfDjRde8aal4dsvD9nb22kaVLc3+sTR65YT3d0ttCHfLsJ53VdwQFznCk10ZjHKcv/s7L8PKEqsatNymlG9k9eZq9rtqyb2XkRgZY7FSxOJqxlGDhLli77tdEfn9/wam/8pH/ABd/2TbUP/TppNf0GV/KP8Mf2Tv2tfgh4hl1fwT8Mf2mPBerXFu1nLfaB4b13S7qWBmV2iaWCNHMZaNGKk7SUU4yox3n2f8A4KEf9Xvf9/PFf+NdHFnCVPN8e8ZTxVOKaSs32+ZzZHnUsDhFh6lCbabei7n9Plfl7/wdRftN/wDCtP2L/DPwys5tmofFLWxJdRlM79O04x3EuG/hb7U9h9V8wetfnX8FLb9vj/hdPg3+1/8AhtH+yf7esPt32yTxR9m+z/aY/N83cdvl7N27dxtznjNesf8ABwN8L/jl+17/AMFENVk8NfBz4ua94N8C6Xa+HtHv9N8IandWWoHabm5uI3WEo2Zp2hLISrLaoQSME+JknCNLAZ3h3XxEJxinNtNWTjZRWr35mn8melmOdzxOXVfZUpRk7Rs1rru9PI+Hf2LP2yPHP7Cfxem8efD210aXxBJps2kLNqemm+jto5Xid2jXcNshEQXdnOx3HRq+rP8AiJb/AGsP+e3gn/wl2/8Ajlfrz/wQ0/ZLvP2Pf+Cb/gjRda0u40fxV4m83xRr1rcwNb3MNzdkNHFNGwDJNFarbQurAMrQkEAjA+u60zzjjK6mOqKrgI1uVuKm5bpO1/henbUzy3hvG08PFQxLhdX5Utm/mfxx/Gfx9qPxo+J/ijxXrlra2mreMNSutY1CO1gaCAz3MryTMiMSVVpGc4yQMkDgYH9HPw//AOCkniLxB/wQwh/aE0CPStX8ceH/AAgZr+PU4pJLWbU7KT7NeNLHG6PtaSKWQAOp2uhzg5Ph3/Bzx+wj4o/aI+H/AMNfiB4B8J+IfF/ibw3fz6FqFhoOly6hey2NynnJM0cSs5jhmgK8Dg3hJ45HJf8ABvd8FvHt5+zZ8dv2ePjJ8NfiZ4P8G+MrSS7s7jWfDt7paTR3ts1jqEMU88SosgRLZ0QZJLyuB8rGvQz7McDnGSYfMnFRdGavC6b5bqMorZtNcr22OfK8HicBmNXCttqpHSVrLm3T6679TT/4JD/8F8/ip+3f+2zpPwz8faL8MtH0nWNLvri1k0axvLe8muoIxKsatNdyqR5azMQEzhM5ABr9bq/mS+Pv/BFL9qr9iX4pNfeHfCXi3xTBot55mieLfARknuZduCk6Q27G8tZQGGQVwrbgskijedOx/aX/AOCi3heNbFZP2po2ClFS78JalPMQAM/NLas5OMHOc9881y51wXl+Y1Y4nJsRShTcVpfrrrpfyumk1Y2y/PsXhYOjj6U5ST3SvofpV/wdTaXokv8AwTv8OXmoQwnVrfxxZQ6TMV/eJI9reGVA3Xa0KOSvQmNT1UY+YP8Ag06GqQfEr4+XWnxmRLfw/pXyMCY3uDLemAMB67ZR1zjNfJXjP9lP9t/9vDxdp8XjDwL+0B4wvrUsLH/hLbK80+ysjJtD+U195NvBv2JuKlc7Fz0FftB/wQr/AOCXer/8E2/2fNdk8ZzafN8Q/iDdwXurxWL+bBpdvAjLa2Xm9JZEMs7u6gIHnZFLqgke80+q5NwxPKpV41Ks2rKLv9pN6XulZbu2rJwftsfnCxipShCKtdq3R/5/cfDv7Ev/AAcz/G79oX9qb4W+DPFHhn4SWOg+OfEWnaLeXGn6XqEdzGt3MkK+Uz3rqGLyKAWVhzyK/cYdK/nP/wCCjH/Bv18av2fvjd4k1b4X+D7zx/8ADO/vpb/R30ErJqGkQyOXWzltN3nM0PKLJCsiuiIxMbuYl4/Rfj1/wUS+HFpFapJ+1hEqkBP7S8N6veyMT0+e5t3ZvxJpZvwnlebRp4nJa1Omraxbtrvru0+jTQ8DnWNwUp0cfTnN30aV9D9tP+C31todz/wSm+Nf/CQLataR6DvtvPxtW9E8X2MjP8f2nyduOd2Mc1+MH/BtnDcSf8FX/CZt8+XHomrNcY/55/ZiP/Qyn6VxnxO+Hf7dn7acNronjPwr+0t4u09pVmistZ0PU7TS/NXO2Rkljjtt65OHbkZOCMmv03/4N9v+COXjb9h7xR4k+KfxYs7XRvGOtaYdC0jQ4b2K7k0y0aZJbiS4eItEZZGgtwixu2xEfcxMhRN/Y4bh/h3E4OtiIVKlW9oxd90o6LfTduyXQy562Z5rRrwpShGnu2reZ+o9FFFfiZ+gBXEftL/8m6+PP+xfvv8A0neu3rif2lEaT9nbx4qjcf8AhH7/AIH/AF7vXk5//wAizE/9e5/+ks9DKf8AfqP+OP5o/NGiiiv88T+xD1D9kH4yaR8Cvi1c65ra3jWc2kzWSi1iEj+Y80DjgkcYjbn1xX0v/wAPHvh//wA+/iP/AMAl/wDi6+GKK/QOG/EzOcjwf1HA8nJdv3o3d3vrddj5DOuCMtzTE/WsVzc1ktHZWXyZ9zS/8FI/h3BE0kkfiCOONSzM1miqijkkkvgADnJ6V4f4u/4OLv2cPCmryWcd54x1hoSVebT9HEkIYHBAdpFD/wC8mVPYmvgf/gpZ4yvvCn7N621lM0I17VYdOuivBaDy5pWXPbc0SA+q7geCa/PWv6K8OeJc5znAvMcwnHlcnGMYxttu22312Stte7vZfyP41cQYbhjNI5Nk9L31FSnObb+K9oxSt0V23feyStd/0F/Cz/g4O+Avxg+KPhnwhpFr4+GreLNXs9EsTcaPHHCJ7qdII97ecdq75FyQDgZOD0r7lr+Wb9gv/k+74H/9lD8Pf+nS2r+pmv1DDVZTvzHznBPEGKzbD1KuKteMrKyt0uFcX8QPjvovw115dO1FL5rh4FuAYYg67WLKOcjnKmu0r5r/AGuf+SrW/wD2C4f/AEbNX5n4ycYZhwzw68zyzl9pzxj7yurO99Lrsfq/DOW0cfjfYV78tm9NNj0T/hrbwr/zz1b/AMB1/wDiqP8Ahrbwr/zz1b/wHX/4qvmuiv5N/wCJleMu9L/wW/8A5I/Rf9Rcs/vff/wD6U/4a28K/wDPPVv/AAHX/wCKrgP2gvjPo/xQ0HT7fTVvFktbkyv58QQbdjLxye5FeVUV4fEnjrxRnmW1cqxzp+yqJKVoWejT0fM+qOrA8I4DCV44ilzc0drv/gBRRRX42fUH0h+yF/yTO8/7CUn/AKLirR/ao+Ml98BvgfqfiLS7G01LVvtVhpenwXcrRWxur69gsoGlZQWESy3CM+0FiqsBzis79kLj4Z3n/YSk/wDRcVdR8cvg9p3x8+FereE9UutQ0+31NY3jvbB0S70+4ilSaC5iMiPH5kU0cciiRHQsgDo6kqf9RvBeVNcJZW6vw8kb+l9fwP5/4q5nmOIUN7u3rY8B+Pv7Qnxo/Zb8JX114qvPhjdabdah4fsNP8XRaJc2en2El/qq2V1Fcae2oSTSGGGSKZJFnjVy7IQpUFsPxD/wUO174W+J/h7cX+r+BfiB4D1Sx17WPF/iLw/pN1pn9g6fY3Wj2Yuo4pbm4Bjt5dU867eSRQlrFLKuDDsl9H8WfsHzfENr668RfFz4naprEk2kzafqATSIDozadffbomhgWx+zu8kwQSNNFISsaqnljdul8KfDTwU+s6D4/wDEfxZuPiJHYW2seBbXUNbutGGn3p1TUNNt57BxZ2sEMky3mlRWyRgb/MmnicOxQJ+z062D5VzxUnd35U1e8LRt7ujvrulfXlaPlZU6/M7Oy6X12et9e2mz9Tifi3+1b8VNN0nxJq3huXwDptj4c+JumfDx7bVNFu76a4XUdR0mxhvA8d5Cq+X/AGm0jR7T5giCho9xYcT8bP8Ago34q+BWsfFLTdd+JX7P+l+JPhdBCtr4W1KzntNW8dznR7W/22SnUjLCJ57hrWILb3Tb04ErZjHZa78HvgH+x18Irf4a+MPjDZ+EtOufE2meObBfFfizT7S/VdIvtOubWCN51VpbOL+zbSBncPM0YbfMZW82vQfGXwG+HPxV+Gn7Q2izeMJJNH+K0E9p4znttTtD/wAI6J/D9pZN5bFCsB+wLbXA88P/AK4Scxsq1pSq4OFnOm3Do+W11eGt2n/f89Ur3sEqdVrSVn11v0fmvIyPF/7aWq+Hf2wPD/g2Pw9b/wDCCyGx0fxBrUzMJ9I1vUre7ubG1OD5ahUs4o5VbLGTWdO2HBevRv2X/jBffHL4a6lrWo2tnZ3Fj4t8TeHkS33bGh0zXb/TYXO4k73itEdu25mwAMAePj9jD4I/H7WfiBHp3jC48QeOb7XLbxRq2saX4ljn1TQL6UW13pM/kR5t4/IgtrA2vnQNvhtIGbzvmdrfwjl+Gv7PN9rWuWf7SSyeC11fxN4jvdE1HWvDv9j2UsuqyT6oWuFtUuljtL7UlRt9z+5aWGOQksA3LiKOFqUVChFqouW94vXRt23+JvS9tEtjSlKqpXnqnfZr/gbW131Y/wAWfHP4wa945+NL+DZPhrDovwj1GPT4tM1nS717vW3/ALEsNUbN5FchLcM16Yg32aXYE3lXztrj/gr/AMFCNT+P/wAbr6ws/GXw58H6Ims6Ja6ZoGq+H7q81zWLS+0PR9WZluY76OKKRjqUkC5gdVMIY+ZkrXY/Gr9nXw3p/ib4h303x+8YfDKz+KEE3iDWtOtLzw/BA0FtYWGl3N5FJeWEtxHEkKWSySCXbG8yEFDIuec8BaX8Gfgt8ffEGl+D/wBpiz8I3l1rFvfat4AtvEPhuaGOTS9IsrKS2aO4tZL+JF0/SYzKBOroqSuGj6r1UY4aVFtQu+RWai9GlHmu+Vp682tn6q6MZe1U9Xpfuu7tbVdLf5M9A/br/ap8Rfs0eEtF/wCEL8MxeMvFGoPeanLpJL+YdI060kvb+WNU+YysqQ2kP8IudQtd+U3V5l+1B/wURvfhj8UPE1rovjn4X+H/AA1oPw90rxvps2uaNe6nJ4m+2Sav+7hkt7uLyoxHpsJDCKZibgkKdoVrvjr4sfslftE/FaTxxrXx2+GPiObwz4e+xw29v8RbGKz0G0e8iNxdD7POjr9onOnxyNK7Rk21oqqjFjJzfw2+GXwS8K/D7VPE/wAN/wBrCfw34P8ACvhzTPBGpalo3iXwtf6bo+n213fvpdtNcXVlcLC0Q1RraIs4LxxwBvMcM71hcLh6dKKrUpc60d4uzba0vaWy0Xu7t26MK1Sq5PkkrdNe3ldddd+nyfoHiv8Abs1zQvjj8OdH/wCEb0+18MalZaM3jm5nuWmm8OXevNNbaNBBLH+6mLX1ubeUEAgXdrIMK2G53Qf22vGuv/G2bQZvFXwh8Nai/jHUPDFv4B8QWFzp3iR7SG8uLa31KC6lvUS886CKLUUgitF8yCXy1lyPOPIxfsu/sf3fwA8WXcPxX8O/8I3fTWeg/wDCWQeP7UJ4ZubfTrS0023t5xJ9mS4tobW1lhWdJHMg81hIXJPqXhL4D+D/AIteFf8AhJNM+O2veMvhRD4sl8bCyt9U0i+0VL221ZtUMf29Lc3Atbe/j8wRfaAY/JERfyVMVOpTwNOOkGvstyi156X5ru7dl7rsl7y1bI+3lu130f8Aw2n/AIF10OL8X/te/GT4X+FPi1q3iDWvhO1r8MfF2jeDHni8M6hDG0uopoMx1B86i58mCHWZN0A+ZzbAiVQ5Vei8I/tVeOvif4t8OeC/B3jv4S+J9c8RJrOpv4kg8L38el6VaaaNLSS1Nl/aBknuXl1WBg/2mJFj3fKzABoYtZ/Z3/aQ0L4r2HhT9oDwpfXXijW9P+JWuXnhzxdo19ceG30mLSI47pFKzRx2qf2RZtI1wki7pJMsFZVW78L/AA34J/ab1211bwL+1HqHxB8Z+BvtCjW9E1Pwxqd1p1jfpEsllPDa2PkC3llsoplZohN5lvhZQm+MpxoqnzVKfK1veDSTcEk2uR6c93urro1oL3+fljK6fn5u/Xtbp80cz+0H+2x8bPgbo+m+H28I+BZfiFq0FzDYC5lmTTNQnbxXo+iafc4jld4ILi31Vbh4md5IXAQswUlug8ef8FHJNUl+BcvgPSbO9s/ic2matrn9q70n0LS7y9s9PWFkQ5jv2ur7CxyfLt06/BBaLAwdG+C/wH+I3xS0XwlN+0AvjH4laFq140lifFejza1e30XiGx1+4iltIogUMFxo8UJiijjEVukylRIPNXqNO/ZR+B/g/VPifr1v40isZNL8Y2fjbxdcPr1qY/CstlcNrYs5y6kWdmbi7vb90lw4Op3MgkVHTY5fUIxiqlN86bekWk1J2W7vZbx81Z7go4ht8stNOqdrav5vZnn3ww/4KP8Aij4r614Xt73xf8Kvhy2seC/DPiQ2uq+H73VJtRuNUF0ZY4GS+gEaIbdVXcrkmTJPGDu6T/wUl8RW/gb4uNrHh7R7PxH4P1TW7jwoqeaLLxHouna/Po00hLEN9qt5oB9oSMlEW8sZMj7R5adf4S/Yg0X9njVPC9p4a+NHxA8EzXmi6V4KsrMPoMp18aXb3c0KgXWnSO9wbf7VI6wFQUhdwihGNcz+0d8EP2d/DfwePhnx58W9J8DQ6P441jVl1m+8U6bpt9YalrE9zq17pjPOvlGOa31Nx9nkjZ/s7wSg+bHFcLTll1Sqo06bcW1ayd0lv0Td3Zby0btZ2QRjiYwblLXX/gdXt6L5knxW/aw+LHwF8dfGWbXrz4d614Y+C/w/i+I93bWHh+8tL7WLSb+3dllHM99IkMqf2MuZjFIrfaDiNdnz95o3xM+K3wq+N3w/8P8AxC1T4e+JNN+I015p8J8PaDeaTPo15BZSXo3NPeXK3ULRwToWCwMrCM7SGYLp2Pwl+F37Wtj488ZaX4isvHXhb4xeDrfwJqcuj6tDd6TeadaS6qrCCe3yRKzapdxyMJTjy4wAjKxaz4L/AGUr7S/iX4c8TeKviZ44+IVx4PiuP7HttYttKtobe4mi8h7t/sVnbtJP5LTRjJEQWeQ+Xu2Mvn1K+G5XGUVFpNNctm3yJK1lo1O7eq0stVodPs53Ti3v30tfX8Nt/wBT2CiiivBOwKjvLSLULSW3njSaGZDHJG67ldSMEEdwRxUlFJpNWYJtO6PhP4r/APBPvxl4S8QT/wDCLWa+ItFkctbYuoobq3TskolZQxHTcpO7GSFziuT/AOGNPil/0Jl7/wCBtp/8er9GKMV+MYzwJ4frVpVadSrTTd+WMo8q8lzQbt6tn6ZhvFbN6VNU5wpza6tSu/N2klf0SPzn/wCGNPil/wBCZe/+Btp/8eo/4Y0+KX/QmXv/AIG2n/x6v0YxRiuX/iAWRf8AQRW++n/8rOj/AIi3mv8Az5p/dP8A+TPwx/4LJfAXxl8J/wBmzw3f+JNBuNJs7nxTDbRSyXEEgeQ2d24XEbsfuoxyRjjrX5u1+5X/AAdCcfsPeA/+yg23/pr1Ovw1r7rh7hnDZDhP7OwspSim3eVm9deiS/A/kPxnzytm3EksZiIqMnCCtG9tL923+J6v+wX/AMn3fA//ALKH4e/9OltX9TNfyzfsF/8AJ93wP/7KH4e/9OltX9TNfVYPqet4Wf7lW/xL8kFfm7/wUH/bf0H9nP8A4KTL4M8ZXR03w74k8F6Xf2eosC0On3n2vUYnEoAJWOVEiG/ohiG75WZk/SKvwZ/4OZP+Uiuh/wDYgad/6X6nXgcb8MYLiHKKmU4+/JUtqtJJrVSTs9U11TT2aabR9xnvEmLyHDxzTBW54SWj1TT0afk0/VbrVH25p/xr8F6vZx3Fn4y8JXdvKMpLDrNtJG49Qwcg/hWv4a8VaX41v3tNF1PTtZuo4zM8FhcpdSJGCFLlYySFBZRkjGWA7ivwAMasfur+Vfot/wAGxSKv/BQLxVhQP+Le33Qf9RLS6/miP0W8LKVlmMv/AAWv/kzuyP6Q+Kx2NpYOeCiud2upvt25f1P0E/4RvUv+gbqH/gM/+FH/AAjepf8AQN1D/wABn/wr7Yorq/4lRw3/AEMpf+Cl/wDJn6n/AMRDqf8APhf+Bf8AAPif/hG9S/6Buof+Az/4Vf0H4beIPE12sNno+oyMxwXeBo40/wB52AUfia+yaK3w/wBFLARqJ4jMJyj1Spxi2vJuUkv/AAF+hE/EKs4+5RSfm2/wsvzOa+EvgEfDbwNaaYzrNcLmW4kUfK8jHJx7DhR04Ud66Wiiv6jyrLMNluCpZfg48tOlFRiuyirLXrotW9W9WfA4jETr1ZVqjvKTbfqwr5n8M/swx6d/wUS1nWvsutReC9P0yHxjpdj5W3R4/FN99r06+vo228XP2CCMGNWCA391MyGW5aSvpiivZw+KnRU1D7St9/8AVvmctSmptN9Hc+SP2rJ734dftVaz4j0/Uvit4PuNe8G6XpX9qeH/AABceONJ137NearIttPa2tlPNayWpuy+9poVuE1DaoYwMyea/CjxNrnwB/Z9+LXhrxl8LfGHhvxZ8SNG0y/0nw/4M8E6nrGj2kjeDNH0x7CG4soZ7a2WG+sbmAJPMgRI43LeW6uf0BxRivRpZvGNNU5Qvok9Un7rTWtr9Fe7l2jyrQ56mEcpcyl/Tuu/n0t53Pzb0H4O/Gn9nTx9488UeB/DGrL4o8Xapp/w7nZbITW1ukng/wAORad4glycXNnpeqQ38b+XkBL69PJjIrn9R/ZF8S/Cue18O+F/AfiaTw74VtPFelaVGNNmmWS1XxF4JNmGcqfMaa2sLiQMcmUW87/Ntcj9RMUYrrhxNWi4y5FdWvvrZWV/RXt2TsYSyuDTV+/yvv8Af18z83/j/wDs3ePPA+qeKfhrpXhXxBr3g/wj8JfF9h4F1KwsZrmIWGo33h97TQ3cZ/0u0ayuooo8Zezjs2DSSC4K9f4Tt9e8J/Fa18JaDpfxE1KFvixqOtXPhPxX8O5bvQ7CC41+6vbjVrTxAlpDax4WZ9Qtw1xNIpkS2IMmQv3jijFZSz+cqSp1IJ2u/Vvq7p9W27WeujRp9RSk2pPW33Lp/wAPdeTPjL4z/CvxrrPhj48R6LpGoR6rrXx28D6zok8+lzXcEltbf8IZ5l95SNG09rA1pcmTZIg22sw8xNpZea+Knw4+JXhP9ru88b+NLGfxvaeG38Eajdan4Q8H3lvbzWEM3iqF4IbHzry4urm0mvYrmUxSu3lXMG23QoXm+8sUYrGlnc4Q9nyq1redrQW+60h+O2hcsGm73/q7f6n5+/G0XHxd/apb4x6Xofxp0Hwvo7eGNO07xBYeCL9NW0i/s7Txml1cNol5YSXl5aNFrlpaFo7bG6+8xZALWVk9c+C8Hijx1+yZ8bmvtEuZbrXrjVRo2oSeEpvDOreL4m0q3iW7u9OlVZo7nz1ltQXjjMsdnDIECuufqbFGKVbNueCiofDypXd9I2trZO+muvL/AHb6h9Vd7t9/x+dvwv5n5x/tPx6v+1T+yj8PPCvg3wL8SbrxH4B8J65PrZ1bwVquhqEk8E6xpK2UMt9bwfaZpr+9swsVv5hIiLnARSfqz9jr4g3HjA6xDc+Lfip4plt4LU7fGHw8uvCsdmf3obyHm06zFwWONwBkKBEPy78t7hijGKWKzSNaj7Hksle2qb1d3f3futy6b3CjhZQlz39dLbad/wA7/I/P/wAC+KdY+Ffizwrpmn+GfitrmmeEPFesatfeENf+GVzfR+GbdU1SR7nStcjtIYJ5cyBLbZLcSzx3Xkne8jSp538SP2cfjV8N/wBmP4nR6t8OVutb+N3wd8WaN4lTw1qdx4hurvxPNb6lrFpuhFpF9lh87UNatE2vMGaTToA3yxbv1ExRiuqPEDjLmjTXRu73aba2t1d9bu6TbbVyY4G32ttvw/y6WXY+VPjv8fdN+J3i/wCDnjDRfDXxUuNE+Gnj99S8Qed8OPEFreW1tceGtfsIpYrSWyS4ul+03VvG/wBmjl8vzVZ9iZYeb2Pi680r9pv/AIWxN4P+J0fgnVvihd6hBKngTWpdRa1/4QnT9NE8mnJam+iiN7bzRBpYEBMe4ZRkdvvPFGK5aOZ06a5YwduVw+L7Lbb+zvd6Pa2lupVTDSna8le6e3VfPa3T8Twf9iTT73UPFPxv8YSaHreh6J8RPHsetaEur6dNpt7d2kXh7RNOeeS0nVJ7fddWF0AsyI7Kqvt2upPvFAGKK87EVvaz57W0SXokkvnZa+Z1QjyqzCiiisCgooooAKKKKACiiigD82f+DoX/AJMe8B/9lBtv/TXqdfhrX7lf8HQv/Jj3gP8A7KDbf+mvU6/DWvJxH8R/10P598SP+Ry/8Mf1PTf2J9csvDH7afwb1PUry10/TdN8eaDd3d3cyrDBawx6lbvJJI7EKqKoLFiQAASeBX9Jf/DefwN/6LP8J/8AwrtP/wDjtfyz0mweg/KijWdPY4+GeMKuT0Z0adNS5nfVtdLH9TP/AA3n8Df+iz/Cf/wrtP8A/jtfin/wcP8AxT8L/GH9vTRdX8I+JPD/AIq0pPA1hbNe6PqMN9bpKt9qLNGXiZlDhWQlScgOpxgjPwlsHoPypQMU6uIc1Zo6s/47rZpg3hJ0lFNp3TfQK/Rb/g2M/wCUgPir/snt9/6ctLr86a/Rb/g2M/5SA+Kv+ye33/py0uoo/GvU8ng7/kdYf/F+jP3aooor2D+mgooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooA/N/wD4OfNMuLz9hPwZPDDJJDZ+P7SSdlUkQqdO1FAzeg3Mq5PdgO9fhfX9Wn7Tn7N/hj9rj4FeIvh54wguJtB8SW4hma2kEdxayK6yRTxMQQJIpUSRSysu5AGVlJU/jH8Tf+DZj45eG/EdxH4X8TfD3xTo3mFba5uLu4027dMD5pYDDIiHqMJNJ07V5+Joy5+ZLc/H+PuF8fisasZhIOcXFJpbpry7M/OeivvT/iG+/aU/u/Dv/wAH8n/yPR/xDfftKf3fh3/4P5P/AJHrn9lPs/uPgf8AVLOf+geX3HwXRX3p/wAQ337Sn934d/8Ag/k/+R6P+Ib79pT+78O//B/J/wDI9Hsp9n9wf6pZz/0Dy+4+C6/R7/g2H0u4n/bt8ZXywyNa2vgO6gllA+VHk1DT2RSfVhFIR/uH0rE8O/8ABtX+0Vq2qRw3uofDPSbUkeZcS61cS7VzztVLYlmxyAcA+o61+qv/AATK/wCCZ/hn/gm18LNT0vTdTuPEnijxNNFca7rc0At/tZiDrDDFCGby4Y/MkKqWdi0shLEEKutCjPnTa0Pq+DuEcyp5lTxWJpuEIO+u700SW59MUUUV6Z+5hRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQB//2Q==';
                    $img_base64_encoded = preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded);
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
                                            font-size: 10px;
                                            aling-self: center;
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
                                            
                                        }
                                        .table3 > td {
                                          
                                        }
                                    </style>
                                </head>
                                <body> 
                                    <div class="container">
                                        <p><img src="@{$img_base64_encoded}" width="180px"></p>
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


                    //$pdf->writeHTML($img, true, false, true, false, '');
                    //$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
                     $pdf->writeHTML($html, true, 0, true, 0);

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

}
