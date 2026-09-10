<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AccountExists
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
    */
    public function handle(Request $request, Closure $next): Response
    {
        $lines = $request->input('lines');

        /**
         * Validar que el campo 'lines' exista y sea un arreglo.
        */
        if (!is_array($lines)) {
            return ResponseHelper::badRequest("El campo 'lines' es obligatorio y debe ser un arreglo.");
        }

        /**
         * Validar que el asiento afecte al menos a dos cuentas contables (Partida Doble).
        */
        if (count($lines) < 2) {
            return ResponseHelper::badRequest("El asiento contable debe afectar al menos a dos cuentas contables.");
        }

        /**
         * Validar que cada línea tenga un 'account_id' válido y que la cuenta esté activa.
        */
        foreach ($lines as $line) {
            if (!isset($line['account_id'])) {
                return ResponseHelper::badRequest("Cada línea del asiento debe contener el campo 'account_id'.");
            }

            $accountExists = DB::table('accounts')
                ->where('id', $line['account_id'])
                ->where('is_active', true)
                ->exists();

            if (! $accountExists) {
                return ResponseHelper::badRequest("La cuenta con ID '{$line['account_id']}' no existe o se encuentra inactiva.");
            }
        }

        return $next($request);
    }
}