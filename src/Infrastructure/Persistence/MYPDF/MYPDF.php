<?php 

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MYPDF;
use \TCPDF;

class MYPDF extends TCPDF {
    private $id;
    private $fecha;
    //Page header
    public function Header() {

        $html = '';
        $this->SetFont('helvetica', 'B', 20);
        $this->writeHTMLCell($w = 0, $h = 50, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = false, $align = 'C', $autopadding = true);
    }

    // Page footer
    public function Footer() {
        // const $id = datos();
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        $datos = $this->id;
        $fecha = date("d/m/Y");
        $footertext = "<span>Cod : $datos</span> ";
        $footertext2 = "<span>Fecha de impresión: $fecha</span>";
        $this->writeHTMLCell(100, 20, '', '', $footertext, 0, 0, false,true, "L", true);
        $this->writeHTMLCell(100, 20, '', '', $footertext2, 0, 0, false,true, "R", true);

    }
    public function datos($id,): void {
        $this->id = $id;
    }
}