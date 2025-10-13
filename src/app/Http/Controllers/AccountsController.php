<?php

namespace App\Http\Controllers;

use App\Helpers\GetRegister;
use App\Helpers\Message;
use App\Http\Requests\AccountsRequest;
use App\Models\Account;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountsController extends Controller
{

    protected ?string $table = 'accounts';

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('accounts.index', [
            "accounts" => GetRegister::getAll(['id','code', 'name'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounts.create', [
            "accounts" => GetRegister::getAll(['id','code', 'name'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountsRequest $request, Message $messenger)
    {
        try {

            // Realizar el registro de la cuenta
            Account::create($request->validated());

            // Generar el mensaje de sesion
            $messenger('¡La cuenta ha sido creada exitosamente!', 'success'); 
        } catch (Exception $e) {

            // Registrar el error en los log de PHP
            Log::error("Error al crear la cuenta: " . $e->getMessage(), ['exception' => $e]);

            // Generar el mensaje de sesion
            $messenger('Ha ocurrido un error al guardar la cuenta.', 'danger'); 
        }

        return redirect()->route('accounts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('accounts.edit', [
            'accounts' => GetRegister::getAll(['id','code', 'name']),
            'account' => GetRegister::findById($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
