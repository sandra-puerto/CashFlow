<?php

namespace App\Exceptions;

use Exception;

/**
 * Excepción lanzada cuando ocurre un error en la creación de un asiento contable.
*/
class JournalEntryException extends Exception
{
    /**
     * @param string $message Mensaje de error.
     * @param int $code Código HTTP sugerido (422 por defecto).
    */
    public function __construct(string $message = 'Error en el asiento contable.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}