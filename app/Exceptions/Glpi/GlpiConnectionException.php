<?php

namespace App\Exceptions\Glpi;

class GlpiConnectionException extends GlpiException
{
    protected $message = 'Error de conexión con GLPI';
}
