<?php

namespace ITColima\Siitec2\Api\Resources\Preinscripciones;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class PeriodosResource extends AbstractResource
{
    public function getAll(array $params = [])
    {
        $response = $this->_get('/preinscripciones/periodos', $params);
        return MessageHelper::getContent($response);
    }

    public function getById($periodo_id, array $params = [])
    {
        if (is_array($periodo_id)) {
            $periodo_id = join('+', $periodo_id);
        }
        $response = $this->_get("/preinscripciones/periodos/{$periodo_id}", $params);
        return MessageHelper::getContent($response);
    }
}