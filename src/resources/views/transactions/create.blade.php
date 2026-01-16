@extends('layout')

@section('title', 'Crear Asiento')

@section('content_header')
    <h1>Registro de Asiento</h1>
@stop

@section('main')
    <form action="{{ route('web.transactions.store') }}" method="POST">

        {{-- CSRF Token --}}
        @csrf

        <table class="table table-bordered table-sm w-100" id="transactions-table">
            <thead class="table-light">
                <tr>
                    <th>Cuenta</th>
                    <th>Fecha</th>
                    <th class="text-end">Débito</th>
                    <th class="text-end">Crédito</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody class="js-entry">
                <tr data-index="0">
                    {{-- Cuenta contable --}}
                    <td>
                        <select name="transactions[0][account_id]" class="form-select form-select-sm" required>
                            <option value="" selected disabled>Seleccione</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </td>

                    {{-- Fecha y hora --}}
                    <td>
                        <input type="datetime-local" name="transactions[0][datetime]" class="form-control form-control-sm" required>
                    </td>

                    {{-- Débito --}}
                    <td>
                        <input type="text" class="form-control form-control-sm text-end money-input" placeholder="$ 0" data-hidden-name="transactions[0][debit]">
                        <input type="hidden" name="transactions[0][debit]" class="hidden-money">
                    </td>

                    {{-- Crédito --}}
                    <td>
                        <input type="text" class="form-control form-control-sm text-end money-input" placeholder="$ 0" data-hidden-name="transactions[0][credit]">
                        <input type="hidden" name="transactions[0][credit]" class="hidden-money">
                    </td>

                    {{-- Descripción --}}
                    <td>
                        <input type="text" name="transactions[0][description]" class="form-control form-control-sm">
                    </td>

                    {{-- Botón eliminar fila --}}
                    <td>
                        <button type="button" class="btn btn-danger btn-sm js-remove-row">Eliminar</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-warning flex-fill js-add-row" data-table="#transactions-table">Añadir fila</button>
            <button type="submit" class="btn btn-primary flex-fill">Radicar</button>
        </div>

    </form>
@stop

@section('js_additional')
    @vite(['resources/js/form-utils.js'])
@stop
