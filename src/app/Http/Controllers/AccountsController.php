<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use Illuminate\Http\Request;

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
        return response()->json($classes);
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
        return response()->json($accounts);
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
        return response()->json($account);
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

        return response()->json($account);
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

        return response()->json($account);
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

        return response()->json(['message' => 'Cuenta desactivada exitosamente.']);
    }
}
