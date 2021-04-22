<?php
declare(strict_types=1);

namespace App\Domain;

interface LinameRepository
{
    public function setValidUpload($archivo,$body): array;
    
    public function setCargarUpload($archivo,$body,$id_usuario): array;
    
    public function getListLiname($params):array;
    
    public function setActivaInactiva($uuid,$estado,$id_usuario):array;
}
