<?php

namespace ITColima\Siitec2\Api\Resources\Preinscripciones;

use ITColima\Siitec2\Api\AbstractResource;
use ITColima\Siitec2\Api\Model\Preinscripciones\Pago;

class PagosResource extends AbstractResource
{
    /**
     * Registra un pago de preinscripción.
     *
     * @param Pago $pago Objeto con los parámetros de preinscripción.
     * @return void
     */
    public function put(Pago $pago)
    {
        $this->requiresClientAccessToken();
        $response = $this->_put("/preinscripciones/pagos/{$pago->id_aspirante}/{$pago->id_periodo}", null);
        return $response;
    }
}