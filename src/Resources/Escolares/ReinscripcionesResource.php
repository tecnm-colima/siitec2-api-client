<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use ITColima\Siitec2\Api\AbstractResource;
use ITColima\Siitec2\Api\Model\Escolares\Reinscripcion;

class ReinscripcionesResource extends AbstractResource
{
    public function put(Reinscripcion $reins)
    {
        $this->requiresClientAccessToken();
        $response = $this->_put("/escolares/resincripciones/{$reins->id_estudiante}/{$reins->id_periodo}", null);
        return $response;
    }
}
