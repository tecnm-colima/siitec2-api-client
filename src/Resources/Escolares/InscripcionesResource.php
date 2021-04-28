<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use ITColima\Siitec2\Api\AbstractResource;
use ITColima\Siitec2\Api\Model\Escolares\Inscripcion;

class InscripcionesResource extends AbstractResource
{
    /**
     * Registra la inscripción de un estudiante a un periodo
     *
     * @param Inscripcion $reins Objeto que contiene los datos para inscripción.
     * @return void
     */
    public function put(Inscripcion $reins)
    {
        $this->requiresClientAccessToken();
        $response = $this->_put("/escolares/inscripciones/{$reins->id_estudiante}/{$reins->id_periodo}", null);
        return $response;
    }
}
