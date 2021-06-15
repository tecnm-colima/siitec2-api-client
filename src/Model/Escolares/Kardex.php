<?php

namespace ITColima\Siitec2\Api\Model\Escolares;

class Kardex
{
    public $alumno_id;
    public $materia_id;
    public $periodo_id;
    public $oportunidad;
    public $calificacion;

    public function __construct(
        $alumno_id,
        $materia_id,
        $periodo_id,
        $calificacion = '0.00',
        $oportunidad = 1
    ) {
        $this->alumno_id = $alumno_id;
        $this->materia_id = $materia_id;
        $this->periodo_id = $periodo_id;
        $this->calificacion = $calificacion;
        $this->oportunidad = $oportunidad;
    }
}