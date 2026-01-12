<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\AccountRequest;
use App\Models\Account;

class AccountsController extends Controller
{
    /**
     * Listar todas las cuentas de nivel superior (Sin cuenta padre).
     *
     * @return \Illuminate\Http\JsonResponse
    */ 
    public function index()
    {
        $classes = Account::whereNull('parent_id')->get();
        return ResponseHelper::success(['accounts' => $classes]);
    }

    /**
     * Obtener y retornar las cuentas hijas de una cuenta padre específica.
     * 
     * @param  string  $parent_id
     * @return \Illuminate\Http\JsonResponse
    */
    public function getByParent(string $parent_id)
    {
        $accounts = Account::where('parent_id', $parent_id)->get();
        return ResponseHelper::success(['accounts' => $accounts]);
    }

    /**
     * Almacenar una nueva cuenta y devolver la información creada.
     *
     * @param  AccountRequest  $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function store(AccountRequest $request)
    {
        $parentAccount = Account::find($request->input('parent_id'));
        $request->merge(['nature' => $parentAccount->nature]);

        $account = Account::create($request->all());
        
        return ResponseHelper::success(['account' => $account]);
    }

    /**
     * Obtener y retornar la información de una cuenta específica.
     * 
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
    */
    public function show(string $id)
    {
        $account = Account::find($id);

        return ResponseHelper::success(['account' => $account]);
    }

    /**
     * Actualiza una cuenta existente y devuelve la información actualizada.
     *
     * @param  AccountRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
    */
    public function update(AccountRequest $request, string $id)
    {
        $account = Account::find($id);
        $account->update($request->all());

        return ResponseHelper::success(['account' => $account]);
    }

    /**
     * Desactivar una cuenta específica.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
    */
    public function destroy(string $id)
    {
        $account = Account::find($id);
        $account->is_active = false;
        $account->save();

        return ResponseHelper::success([], 'Cuenta desactivada correctamente.');
    }
}