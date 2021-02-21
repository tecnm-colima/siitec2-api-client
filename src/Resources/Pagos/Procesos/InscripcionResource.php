<?php

namespace ITColima\Siitec2\Api\Resources\Pagos\Procesos;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class InscripcionResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $response = $this->_get('/pagos/procesos/inscripcion', $params);
        return MessageHelper::getContent($response);
    }

    public function getById($id_proceso, array $params = [])
    {
        if (is_array($id_proceso)) {
            $id_proceso = join('+', $id_proceso);
        }
        $response = $this->_get("/pagos/procesos/inscripcion/{$id_proceso}", $params);
        return MessageHelper::getContent($response);
    }

    public function getCurrent(array $params = [])
    {
        $response = $this->_get('/pagos/procesos/inscripcion/@current', $params);
        return MessageHelper::getContent($response);
    }
}