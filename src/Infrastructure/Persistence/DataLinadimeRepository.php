<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Actions\RepositoryConection\Conect;
use App\Domain\LinadimeRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Domain\DatosGeneralesRepository;
use App\Infrastructure\Persistence\DataDatosGeneralesRepository;
use App\Infrastructure\Persistence\DataCorrelativoRepository;
use \PDO;

class DataLinadimeRepository implements LinadimeRepository {

    /**
     * @var $db conection db
     */
    private $db;
    private $datosGeneralesRepository;
    private $dataCorrelativoRepository;

    /**
     * DataMenuRepository constructor.
     *
     */
    public function __construct() {
        $con = new Conect();
        $this->db = $con->getConection();
        $this->datosGeneralesRepository = new DataDatosGeneralesRepository;
        $this->dataCorrelativoRepository = new DataCorrelativoRepository;
    }

    public function setValidUpload($archivo, $body): array {
        $comentario = $body['descripcion'];
        $newfile = $archivo['uploadFile'];
        if ($newfile->getError() === UPLOAD_ERR_OK) {
            $newfile->getClientFilename();
            $extencionFile = $newfile->getClientFilename();
            $extencionFile = explode(".", $extencionFile);
            if (count($extencionFile) >= 2) {
                $extencionFile = $extencionFile[count($extencionFile) - 1];
            } else {
                return array('error' => true);
            }
            if ($extencionFile == 'xls' || $extencionFile == 'xlsx') {
                $fverif = $newfile->getFilePath();
                $spreadsheet = IOFactory::load($fverif);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                if (isset($sheetData[5], $sheetData[6])) {
                    $verifCab = $sheetData[5];
                    $verifCab2 = $sheetData[6];
                } else {
                    return array('error' => 'Cabecera incorrecta1');
                }

                if (!isset($verifCab['B'], $verifCab['C'], $verifCab['D'], $verifCab['E'], $verifCab2['F'], $verifCab2['G'], $verifCab2['H'])) {
                    return array('error' => 'Cabecera incorrecta2');
                }

                if (
                        stristr((string) $verifCab['B'] . '', "CÓDIGO") === FALSE &&
                        stristr((string) $verifCab['C'] . '', "DISPOSITIVO") === FALSE &&
                        stristr((string) $verifCab['D'] . '', "ESPECIFICACIÓN TÉCNICA") === FALSE &&
                        stristr((string) $verifCab['E'] . '', "PRESENTACIÓN") === FALSE &&
                        stristr((string) $verifCab2['F'] . '', "I") === FALSE &&
                        stristr((string) $verifCab2['G'] . '', "II") === FALSE &&
                        stristr((string) $verifCab2['H'] . '', "III") === FALSE
                ) {
                    return array('error' => 'error en cabezera');
                }
                $datosV = 0;
                $datosNV = 0;
                $obs = array();

                for ($ii = 7; $ii <= count($sheetData); $ii++) {
                    if (isset(($sheetData[$ii])['B'])) {
                        $B = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['B']);
                    } else {
                        $B = '';
                    }
                    if (isset(($sheetData[$ii])['C'])) {
                        $C = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['C']);
                    } else {
                        $C = '';
                    }
                    if (isset(($sheetData[$ii])['D'])) {
                        $D = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['D']);
                    } else {
                        $D = '';
                    }
                    if (isset(($sheetData[$ii])['E'])) {
                        $E = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['E']);
                    } else {
                        $E = '';
                    }
                    if (isset(($sheetData[$ii])['F'])) {
                        $F = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['F']);
                    } else {
                        $F = '';
                    }
                    if (isset(($sheetData[$ii])['G'])) {
                        $G = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['G']);
                    } else {
                        $G = '';
                    }
                    if (isset(($sheetData[$ii])['H'])) {
                        $H = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['H']);
                    } else {
                        $H = '';
                    }

                    if ($B != '' && $C != '' && $D != '' && $E != '') {
                        $datosV++;
                    } else {
                        ($sheetData[$ii])['fila'] = $ii + 1;
                        array_push($obs, $sheetData[$ii]);
                        $datosNV++;
                    }
                }
                $data = array('valid' => $datosV, 'invalid' => $datosNV, 'mensaje' => 'Datos del documento', 'obs' => $obs);
                return $data;
            }
        } else {
            return array('error' => 'Extencion no valida');
        }
    }

    public function setCargarUpload($archivo, $body, $id_usuario): array {
        $comentario = $body['descripcion'];
        $newfile = $archivo['uploadFile'];
        if ($newfile->getError() === UPLOAD_ERR_OK) {
            $newfile->getClientFilename();
            $extencionFile = $newfile->getClientFilename();
            $extencionFile = explode(".", $extencionFile);
            if (count($extencionFile) >= 2) {
                $extencionFile = $extencionFile[count($extencionFile) - 1];
            } else {
                return array('error' => true);
            }
            if ($extencionFile == 'xls' || $extencionFile == 'xlsx') {


                $fverif = $newfile->getFilePath();
                $spreadsheet = IOFactory::load($fverif);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                if (isset($sheetData[5], $sheetData[6])) {
                    $verifCab = $sheetData[5];
                    $verifCab2 = $sheetData[6];
                } else {
                    return array('error' => 'Cabecera incorrecta');
                }
                if (!isset($verifCab['B'], $verifCab['C'], $verifCab['D'], $verifCab['E'],
                                $verifCab2['F'], $verifCab2['G'], $verifCab2['H'])) {
                    return array('error' => 'Cabecera incorrecta');
                }

                if (
                        stristr((string) $verifCab['B'] . '', "CÓDIGO") === FALSE &&
                        stristr((string) $verifCab['C'] . '', "DISPOSITIVO") === FALSE &&
                        stristr((string) $verifCab['D'] . '', "ESPECIFICACIÓN TÉCNICA") === FALSE &&
                        stristr((string) $verifCab['E'] . '', "PRESENTACIÓN") === FALSE &&
                        stristr((string) $verifCab2['F'] . '', "I") === FALSE &&
                        stristr((string) $verifCab2['G'] . '', "II") === FALSE &&
                        stristr((string) $verifCab2['H'] . '', "III") === FALSE
                ) {
                    return array('error' => 'Cabezera incorrecta');
                }
                $datosV = 0;
                $datosNV = 0;
                $obs = array();

                $sql = "SELECT UUID() as uuid;";
                $uuid = $this->db->prepare($sql);
                $uuid->execute();
                $uuid = $uuid->fetch();
                $uuid = $uuid["uuid"];
                try {  
                    $this->db->beginTransaction();

                    for ($ii = 7; $ii <= count($sheetData); $ii++) {
                        if (isset(($sheetData[$ii])['B'])) {
                            $B = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['B']);
                        } else {
                            $B = '';
                        }
                        if (isset(($sheetData[$ii])['C'])) {
                            $C = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['C']);
                        } else {
                            $C = '';
                        }
                        if (isset(($sheetData[$ii])['D'])) {
                            $D = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['D']);
                        } else {
                            $D = '';
                        }
                        if (isset(($sheetData[$ii])['E'])) {
                            $E = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['E']);
                        } else {
                            $E = '';
                        }
                        if (isset(($sheetData[$ii])['F'])) {
                            $F = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['F']);
                        } else {
                            $F = '';
                        }
                        if (isset(($sheetData[$ii])['G'])) {
                            $G = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['G']);
                        } else {
                            $G = '';
                        }
                        if (isset(($sheetData[$ii])['H'])) {
                            $H = str_replace(array("\r\n", "\r", "\n"), "", (string) ($sheetData[$ii])['H']);
                        } else {
                            $H = '';
                        }

                        $sql = " INSERT INTO param_linadime(
                                    u_crea,
                                    id_param_liname_archivo,

                                    codigo,
                                    dispositivo,
                                    esp_tec,
                                    
                                    presen,
                                    niv_uso_I,
                                    niv_uso_II,
                                    
                                    niv_uso_III

                                    )
                                    VALUES (
                                        :u_crea,
                                        :id_param_liname_archivo,

                                        REPLACE(REPLACE(REPLACE(:B, '\n', ''),'\r',''),'\t',''),
                                        REPLACE(REPLACE(REPLACE(:C, '\n', ''),'\r',''),'\t',''),
                                        REPLACE(REPLACE(REPLACE(:D, '\n', ''),'\r',''),'\t',''),

                                        REPLACE(REPLACE(REPLACE(:E, '\n', ''),'\r',''),'\t',''),
                                        REPLACE(REPLACE(REPLACE(:F, '\n', ''),'\r',''),'\t',''),
                                        REPLACE(REPLACE(REPLACE(:G, '\n', ''),'\r',''),'\t',''),

                                        REPLACE(REPLACE(REPLACE(:H, '\n', ''),'\r',''),'\t','')
                                        )
                                        ";
                        $query = $this->db->prepare($sql);
                        $query->bindParam(':u_crea', $id_usuario, PDO::PARAM_STR);
                        $query->bindParam(':id_param_liname_archivo', $uuid, PDO::PARAM_STR);

                        $query->bindParam(':B', $B, PDO::PARAM_STR);
                        $query->bindParam(':C', $C, PDO::PARAM_STR);
                        $query->bindParam(':D', $D, PDO::PARAM_STR);

                        $query->bindParam(':E', $E, PDO::PARAM_STR);
                        $query->bindParam(':F', $F, PDO::PARAM_STR);
                        $query->bindParam(':G', $G, PDO::PARAM_STR);

                        $query->bindParam(':H', $H, PDO::PARAM_STR);

                        $query->execute();

                        if ( $B == '' && $C == '' && $D == '' && $E == '') {
                            $datosV++;
                        }
                    }

                    //
                    $ruta = $this->datosGeneralesRepository->getDatosCodigo('LINADIME_FILE');
                    $ruta = $ruta['recurso'];

                    if (!file_exists($ruta)) {
                        if (!mkdir($ruta, 0777, true)) {
                            $this->db->rollBack();
                            return array('error' => 'permisos');
                        }
                    }else{
                        return array('error' => 'permisos');
                    }

                    $uploadFileName = $uuid . '--' . $id_usuario;
                    $name = $uploadFileName . '.' . $extencionFile;
                    $newfile->moveTo($ruta . $name);
                    $sql = " INSERT INTO param_linadime_archivo (
                        id,
                        u_crea,
                        codigo,
                        nombre_archivo,
                        comentario
                        )VALUE(
                        :id,
                        :u_crea,
                        :codigo,
                        :nombre_archivo,
                        :comentario
                        )";

                    //call correlativo
                    $correlativo = $this->dataCorrelativoRepository->genCorrelativo('LIND', '0', $id_usuario);
                    $correlativo = $correlativo['correlativo'];
                    $correlativo = 'LIND-' . $correlativo;

                    $query = $this->db->prepare($sql);

                    $query->bindParam(':id', $uuid, PDO::PARAM_STR);
                    $query->bindParam(':u_crea', $id_usuario, PDO::PARAM_STR);
                    $query->bindParam(':codigo', $correlativo, PDO::PARAM_STR);
                    $query->bindParam(':nombre_archivo', $name, PDO::PARAM_STR);
                    $query->bindParam(':comentario', $comentario, PDO::PARAM_STR);
                    $query->execute();
                    $sql = "UPDATE param_linadime_archivo SET activo=false WHERE id!=:id";
                    $query = $this->db->prepare($sql);
                    $query->bindParam(':id', $uuid, PDO::PARAM_STR);
                    $query->execute();
                } catch (\Exception $ex) {
                    $this->db->rollBack();
                    return array('error' => 'Datos incorrectos');
                }
                if ($datosV != 0) {
                    return array('error' => 'Datos incorrectos');
                }
                $this->db->commit();
                return array('mensaje' => 'Datos Correctos');
            } else {
                return array('error' => 'Extencion no valida');
            }
        } else {
            return array('error' => 'Error en archivo');
        }
    }

    public function getListLinadime($params): array {


        $filtro = $params['filtro'];
        $indice = $params['indice'];
        $limite = $params['limite'];
        $estado = strtolower($filtro);
        if (str_contains($estado, 'a') || str_contains($estado, 'ac') || str_contains($estado, 'act') || str_contains($estado, 'acti') || str_contains($estado, 'activ') || str_contains($estado, 'activo')) {
            $activo = 1;
        } else {
            $activo = null;
        }
        if (str_contains($estado, 'i') || str_contains($estado, 'in') || str_contains($estado, 'ina') || str_contains($estado, 'inac') || str_contains($estado, 'inact') || str_contains($estado, 'inacti') || str_contains($estado, 'inactiv') || str_contains($estado, 'inactivo')) {
            $activo = 0;
        } else {
            $activo = null;
        }
        $limite = $indice + $limite;
        $filtro = '%' . $filtro . '%';
        $sql = "SELECT CASE WHEN activo = 1
                            THEN 'activo'
                            ELSE 'inactivo'
                        END as activo,codigo,id,comentario,f_crea
                FROM param_linadime_archivo
                WHERE activo=:activo OR codigo LIKE :codigo OR comentario LIKE :comentario OR DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filtro
                ORDER BY f_crea DESC
                LIMIT :indice, :limite;";
        $query = $this->db->prepare($sql);
        $query->bindParam(':activo', $activo, PDO::PARAM_INT);
        $query->bindParam(':codigo', $filtro, PDO::PARAM_STR);
        $query->bindParam(':comentario', $filtro, PDO::PARAM_STR);
        $query->bindParam(':filtro', $filtro, PDO::PARAM_STR);
        $query->bindParam(':limite', $limite, PDO::PARAM_INT);
        $query->bindParam(':indice', $indice, PDO::PARAM_INT);
        //$query->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() > 0) {
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $res = array();
        }
        $sql = "SELECT activo
                FROM param_linadime_archivo
                WHERE activo=:activo OR codigo LIKE :codigo OR comentario LIKE :comentario OR DATE_FORMAT(f_crea,'%d/%m/%Y') LIKE :filtro; ";
        $query = $this->db->prepare($sql);
        $query->bindParam(':activo', $activo, PDO::PARAM_INT);
        $query->bindParam(':codigo', $filtro, PDO::PARAM_STR);
        $query->bindParam(':comentario', $filtro, PDO::PARAM_STR);
        $query->bindParam(':filtro', $filtro, PDO::PARAM_STR);
        $query->execute();
        return array('total' => $query->rowCount(), 'resultados' => $res);
    }

    public function setActivaInactiva($uuid, $estado, $id_usuario): array {
        $est = "-1";
        $estAnt = "-1";
        if ($estado == 'activo') {
            $est = "1";
            $estAnt = "0";
        }
        if ($estado == 'inactivo') {
            $est = "0";
            $estAnt = "1";
        }

        $sql = "SELECT * FROM param_linadime_archivo WHERE id=:id";
        $query = $this->db->prepare($sql);
        $query->bindParam(':id', $uuid, PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) != 1) {
            return array('error' => 'Registro incorrecto');
        }
        $estadoSelec = ($res[0])['activo'];
        if ($estadoSelec == 0 && $estado == 'inactivo') {
            $sql = "UPDATE param_linadime_archivo SET activo=1 WHERE id=:id";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $uuid, PDO::PARAM_INT);
            $query->execute();

            $sql = "UPDATE param_linadime_archivo SET activo=0, f_mod=now(), u_mod=:u_mod WHERE id!=:id and activo=1";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $uuid, PDO::PARAM_INT);
            $query->bindParam(':u_mod', $id_usuario, PDO::PARAM_STR);
            $query->execute();

            return array('id' => $uuid, 'estado' => $estado);
        }
        if ($estadoSelec == 1 && $estado == 'activo') {
            $sql = "UPDATE param_liname_archivo SET activo=0 WHERE id=:id";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $uuid, PDO::PARAM_INT);
            $query->execute();
            return array('ejecutado' => $query->rowCount());
        }
        return array('error' => 'Datos invalidos');
    }

    public function gerArchive($uuid): array {
        $sql = "SELECT nombre_archivo FROM param_linadime_archivo WHERE id=:id";
        $query = $this->db->prepare($sql);
        $query->bindParam(':id', $uuid, PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) != 1) {
            return array('error' => 'Registro incorrecto');
        }

        $ruta = $this->datosGeneralesRepository->getDatosCodigo('LINADIME_FILE');
        $ruta = $ruta['recurso'];

        $pathArch = $ruta . ($res[0])['nombre_archivo'];
        if (!file_exists($pathArch)) {
            return array('error' => "No se encuentra el archivo");
        }
        return array('nombre_archivo' => ($res[0])['nombre_archivo'], 'ruta' => $ruta);
    }

}
