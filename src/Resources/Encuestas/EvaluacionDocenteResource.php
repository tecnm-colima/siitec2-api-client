<?php

namespace ITColima\Siitec2\Api\Resources\Encuestas;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class EvaluacionDocenteResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $response = $this->_get('/encuestas/edocente', $params);
        return MessageHelper::getContent($response);
    }

    public function getRealizada($encuesta_id, $usuario_id, array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $response = $this->_get("/encuestas/edocente/{$encuesta_id}/usuarios/{$usuario_id}", $params);
        return MessageHelper::getContent($response);
    }
}