<?php

namespace ITColima\Siitec2\Api\Resources\App;

use ITColima\Siitec2\Api\AbstractResource;
use Francerz\Http\Utils\MessageHelper;

class UsuariosResource extends AbstractResource
{
    public function getById($id, array $params = [])
    {
        if (is_array($id)) {
            $id = join('+', $id);
        }
        $this->requiresClientAccessToken(true);
        $response = $this->_get("/app/usuarios/{$id}", $params);
        return MessageHelper::getContent($response);
    }

    /**
     * Find a user by given parameter
     *
     * @param array $params
     *  - term: Termino de búsqueda
     *  - matricula: Número de control o ID de aspirante
     *  - rol: Tipo de usuario (alumno, aspirante, empleado)
     *  - curp: CURP del usuario
     *  - correo: Dirección de correo electrónico
     *  - usuario: Nombre de usuario utilizado para ingresar a SIITEC
     *  - nombre: Nombre o Apellidos del usuario
     * @return array
     */
    public function find(array $params=[])
    {
        $this->requiresClientAccessToken(true);
        $response = $this->_get('/app/usuarios', $params);
        return MessageHelper::getContent($response);
    }

    public function findTerm($term, array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $params['q'] = $term;
        $response = $this->_get('/app/usuarios', $params);
        return MessageHelper::getContent($response);
    }

    public function findMatricula($matricula, array $params = [])
    {
        $this->requiresClientAccessToken(true);
        $params['matricula'] = $matricula;
        $response = $this->_get('/app/usuarios', $params);
        return MessageHelper::getContent($response);
    }

    public function findCurp($curp, array $params = []) 
    {
        $this->requiresClientAccessToken(true);
        $params['curp'] = $curp;
        $response = $this->_get('/app/usuarios', $params);
        return MessageHelper::getContent($response);
    }
}