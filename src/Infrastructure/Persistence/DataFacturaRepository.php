<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use \PDO;
use AbmmHasan\Uuid;
use App\Domain\FacturaRepository;
use App\Infrastructure\Persistence\Factura\CodigoControl;
use App\Infrastructure\Persistence\DataCorrelativoRepository;

class DataFacturaRepository implements FacturaRepository {

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

    public function getFactura($id,$id_usuario): array {
        // add datos venta service repository
        $venta = array('success' => true);
        if ($venta['success']) {
            
        }
        ///
        $nitci = $_ENV['NIT_CEASS'];
        $autorizacion = "";
        $llave = "";
        $fecha = "";
        $id_docificacion = "";

        $nrofactura = "";
        $monto = "";
        $venta = "";

        try {
            iniVerif:
            $sql = "SELECT * 
                    FROM fac_factura 
                    WHERE id_venta=:id_venta and activo=true AND estado='GENERADO'
                    LIMIT 1;";
            $res = ($this->db)->prepare($sql);
            $res->execute();
            $res = $res->fetchAll(PDO::FETCH_ASSOC);
            if (count($res) == 0) {
                $CodigoControl = new CodigoControl();
                $fechaCC = str_replace("/", "", $fecha);
                $montoCC = round(str_replace(",", ".", $monto), 0);
                $newcodigo = $CodigoControl->generar($autorizacion, $nrofactura, $nitci, $fechaCC, $montoCC, $llave);

                $uuid = Uuid::v4();
                $corrRep = new DataCorrelativoRepository;
               
                $nro_factura = $this->dataCorrelativoRepository->genCorrelativo('FAC', $id_docificacion, $id_usuario);
                $nro_factura = $nro_factura['correlativo'];

                $sql = "INSERT INTO fac_factura (
                        id,
                        nro_factura,
                        codigo_control,
                        monto,
                        id_venta,
                        id_dosificacion,
                        estado,
                        u_crea
                    ) 
                      VALUES(
                        :id,
                        :nro_factura,
                        :codigo_control,
                        :monto,
                        :id_venta,
                        :id_dosificacion,
                        :estado,
                        :u_crea,
                      )";
                $res = ($this->db)->prepare($sql);
                $res->bindParam(':id', $uuid, PDO::PARAM_STR);
                $res->bindParam(':nro_factura', $nro_factura, PDO::PARAM_INT);
                $res->bindParam(':codigo_control', $monto, PDO::PARAM_STR);
                $res->bindParam(':monto', $monto, PDO::PARAM_STR);
                $res->bindParam(':id_venta', $id_venta, PDO::PARAM_STR);
                $res->bindParam(':id_dosificacion', $id_dosificacion, PDO::PARAM_STR);
                $res->bindParam(':estado', $estado, PDO::PARAM_STR);
                $res->bindParam(':u_crea', $u_crea, PDO::PARAM_STR);
                $res->execute();
                goto iniVerif;
            }
            $res = $res[0];
            //llamado a factura template
            
            
        } catch (\Exception $e) {
            return array('success' => false);
        }


        return array();
    }

}
