<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Http\Requests\TransactionRequest;
use App\Models\Account;
use App\Services\TransactionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('transactions.index');   
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('transactions.create', [
            'accounts' => Account::all('code', 'name', 'id')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        //return dd($request->transactions);
        $execute = $this->transactionService->executeJournalEntry($request->transactions);

        return redirect()->route('web.transactions.index')->with('message', [
            'success' => $execute->success,
            'message' => $execute->message
        ]);
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
        //
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
