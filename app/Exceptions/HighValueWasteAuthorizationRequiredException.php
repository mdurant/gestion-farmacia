<?php

namespace App\Exceptions;

use Exception;

class HighValueWasteAuthorizationRequiredException extends Exception
{
    public function __construct(float $totalValue, int $threshold)
    {
        parent::__construct(sprintf(
            'La merma por $%s requiere autorización de un supervisor (umbral $%s). Solicite un código de un solo uso.',
            number_format($totalValue, 0, ',', '.'),
            number_format($threshold, 0, ',', '.'),
        ));
    }
}
