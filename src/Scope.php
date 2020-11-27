<?php

namespace ITColima\Siitec2\Api;

interface Scopes
{
    const GET_FULL_ACCESS_USER = 'self.all:r';
    const GET_FULL_ACCESS_APP = 'app.all:r';

    const GET_USUARIO_PERFIL_OWN= 'usuarios.perfil.own:r';
    const GET_USUARIO_PERFIL_BY_ID = 'usuarios.perfil.by-id:r';

    const GET_DOCENCIA_GRUPOS_ALL = 'docencia.grupos.all:r';
    const GET_DOCENCIA_GRUPOS_USUARIO = 'docencia.grupos.own:r';
    const GET_DOCENCIA_GRUPOS_BY_ID = 'docencia.grupos.by-id:r';

    const GET_ESCOLARES_PERIODOS_ALL = 'escolares.periodos.all:r';
    const GET_ESCOLARES_ASIGNATURAS_ALL = 'escolares.asignaturas.all:r';
    const GET_ESCOLARES_PLANES_ESTUDIO_ALL = 'escolares.planes-estudio.all:r';
}