<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use Francerz\Http\Utils\MessageHelper;
use ITColima\Siitec2\Api\AbstractResource;

class PeriodosResource extends AbstractResource
{
    /**
     * Obtiene los periodos escolares según los parámetros proporcionados
     *
     * @param array $params
     *  - fin_desde: Periodos que terminen a partir del a fecha proporcionada
     * @return array
     */
    public function getAll(array $params = [])
    {
        $this->requiresAccessToken(false);
        $response = $this->_get('/escolares/periodos', $params);
        return MessageHelper::getContent($response);
    }

    public function getById($periodo_id, array $params = [])
    {
        if (is_array($periodo_id)) {
            $periodo_id = join('+', $periodo_id);
        }
        $this->requiresAccessToken(false);
        $response = $this->_get("/escolares/periodos/{$periodo_id}", $params);
        return MessageHelper::getContent($response);
    }

    public function getCurrent(array $params = [])
    {
        $this->requiresAccessToken(false);
        $response = $this->_get('/escolares/periodos/@current', $params);
        return MessageHelper::getContent($response);
    }
}