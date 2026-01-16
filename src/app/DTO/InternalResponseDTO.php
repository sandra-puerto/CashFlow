<?php

namespace App\DTO;

/**
 * DTO interno para estandarizar la comunicación entre Services y Controllers.
 */
class InternalResponseDTO
{
    /**
     * Indica si la operación fue exitosa.
    */
    public bool $success;

    /**
     * Mensaje descriptivo de la operación.
    */
    public ?string $message;

    /**
     * Datos devueltos por el Service.
    */
    public array $data;

    /**
     * Código HTTP oficial.
     * Ejemplos:
     *  - 200 → OK
     *  - 201 → Created
     *  - 400 → Bad Request
     *  - 401 → Unauthorized
     *  - 404 → Not Found
     *  - 500 → Internal Server Error
    */
    public int $code;

    /**
     * Constructor del DTO.
     *
     * @param bool $success
     * @param string|null $message
     * @param array $data
     * @param int $code Código HTTP oficial (default: 200 OK)
    */
    public function __construct(
        bool $success,
        ?string $message = null,
        array $data = [],
        int $code = 200
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->data    = $data;
        $this->code    = $code;
    }
}
