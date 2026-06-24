<?php

namespace App\Exceptions;

use Exception;

class ControlledDrugAuthorizationRequiredException extends Exception
{
    public function __construct(string $drugName)
    {
        parent::__construct("Se requiere autorización del Director Médico para manipular el fármaco controlado: {$drugName}.");
    }
}
