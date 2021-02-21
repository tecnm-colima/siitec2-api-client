<?php

namespace ITColima\Siitec2\Api\Resources\Pagos\Procesos;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class PreinscripcionResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $response = $this->_get('/pagos/procesos/preinscripcion', $params);
        return MessageHelper::getContent($response);
    }

    public function getById($id_proceso, array $params = [])
    {
        if (is_array($id_proceso)) {
            $id_proceso = join('+', $id_proceso);
        }
        $response = $this->_get("/pagos/procesos/preinscripcion/{$id_proceso}", $params);
        return MessageHelper::getContent($response);
    }

    public function getCurrent(array $params = [])
    {
        $response = $this->_get('/pagos/procesos/preinscripcion/@current', $params);
        return MessageHelper::getContent($response);
    }
}