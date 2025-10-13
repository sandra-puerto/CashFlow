<?php

namespace App\Helpers;

class Message{

    /**
     * @abstract Envía un mensaje flash de única visualización a la sesión de Laravel.
     * 
     * @param string $message El contenido del mensaje a mostrar.
     * @param string $type La clase CSS del mensaje (ej. 'success', 'danger', 'warning').
     * 
     * @return void
     */
    public function __invoke(string $message, string $type = "success"): void
    {
        session()->flash('notification', [
            'type' => $type,
            'text' => $message
        ]);
    }
}