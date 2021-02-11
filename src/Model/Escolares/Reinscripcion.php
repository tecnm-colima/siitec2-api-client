<?php

namespace ITColima\Siitec2\Api\Model\Escolares;

class Reinscripcion
{
    public $id_estudiante;
    public $id_periodo;

    public function __construct($id_estudiante = null, $id_periodo = null)
    {
        $this->id_estudiante = $id_estudiante;
        $this->id_periodo = $id_periodo;
    }
}