<?php

namespace App\Exceptions\Glpi;

class GlpiAuthenticationException extends GlpiException
{
    protected $message = 'Error de autenticación con GLPI';
}
