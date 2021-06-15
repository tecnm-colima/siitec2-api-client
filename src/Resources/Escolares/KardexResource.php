<?php

namespace ITColima\Siitec2\Api\Resources\Escolares;

use Francerz\Http\Utils\Constants\MediaTypes;
use Francerz\Http\Utils\HttpHelper;
use ITColima\Siitec2\Api\AbstractResource;
use ITColima\Siitec2\Api\Model\Escolares\Kardex;
use LogicException;

class KardexResource extends AbstractResource
{
    /**
     * Agrega registro de calificaciones en el Kardex
     *
     * @param int $periodo_id
     * @param Kardex|Kardex[] $kardex
     * @param bool $overwrite
     * @return void
     */
    public function post($periodo_id, $kardex, $overwrite = false)
    {
        $this->requiresClientAccessToken(true);
        if (!is_array($kardex)) {
            $kardex = [$kardex];
        }
        foreach ($kardex as $i => $k) {
            if (!$k instanceof Kardex) {
                throw new LogicException("Invalid kardex value, MUST be Kardex or Kardex[].");
            }
            if (!isset($k->alumno_id, $k->materia_id, $k->periodo_id)) {
                throw new LogicException("Undefined alumno_id, materia_id or periodo_id on row {$i}.");
            }
            if (!is_numeric($k->calificacion) || $k->calificacion < 0 || $k->calificacion > 100) {
                throw new LogicException(sprintf('Calificación invalida %d en posición %d.', $k->calificacion, $i));
            }
            if (!is_numeric($k->oportunidad) || !in_array($k->oportunidad, [1,2,'1','2'])) {
                throw new LogicException(sprintf('Oportunidad inválida %d en posición %d.', $k->oportunidad, $i));
            }
        }

        $response = $this->_post("/escolares/kardex/",
            array(
                "periodo_id" => $periodo_id,
                "overwrite" => $overwrite,
                "kardex" => $kardex
            ), MediaTypes::APPLICATION_JSON);
        
        return HttpHelper::getContent($response);
    }
}