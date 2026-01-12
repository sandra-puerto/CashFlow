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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $data = json_decode($request->getContent(), true);

        /**
         * Validar que el campo 'transactions' exista y sea un arreglo.
        */
        if(!array_key_exists('transactions', $data) || !is_array($data['transactions']))
        {
            return ResponseHelper::badRequest("El campo 'transactions' es obligatorio y debe ser un arreglo.");
        }

        /**
         * Validar que el cliente afecte al menos a dos cuentas contables.
        */
        if(count($data['transactions']) < 2){
            return ResponseHelper::badRequest("Se debe afectar al menos a dos cuentas contables en la transacción.");
        }

        /**
         * Validar que cada transacción tenga un 'account_id' válido.
        */
        foreach($data['transactions'] as $transaction){
            if(!array_key_exists('account_id', $transaction)){
                return ResponseHelper::badRequest("Cada transacción debe contener el campo 'account_id'.");
            }

            $accountExists = DB::table('accounts')->where('id', $transaction['account_id'])->exists();

            if(!$accountExists){
                return ResponseHelper::badRequest("La cuenta con ID '{$transaction['account_id']}' no existe.");
            }
        }

        return $next($request);
    }
}
