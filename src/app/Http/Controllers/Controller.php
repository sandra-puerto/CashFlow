<?php

namespace App\Http\Controllers;

use App\Helpers\GetRegister;

abstract class Controller
{
    /**
     * @var string|null Nombre de la tabla que será consultada.
     *                  Debe ser definida en la clase hija para especificar la tabla correspondiente.
     */
    protected ?string $table;

    /**
     * @method __construct
     * @abstract Inicializa la clase y actualiza el nombre de la tabla en GetRegister
     *           solo si la propiedad `$table` está definida en la clase hija.
     */
    public function __construct()
    {
        if (isset($this->table)) {
            GetRegister::setTable($this->table);
        }
    }

}
