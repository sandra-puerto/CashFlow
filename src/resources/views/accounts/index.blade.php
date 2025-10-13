@extends('layouts.base')

{{-- Complemento titulo header --}}
@section('title', 'Cuentas')

{{-- Clases adicionales body --}}
@section('body_class','text-center')

@section('main')
    <div class="container mt-5">
        <div class="card">
            <div class="card-header fw-bold fst-italic">Cuentas Contables</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Codigo</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Acciones</th> </tr>
                        </thead>
                        <tbody class="align-middle">
                            @foreach ($accounts as $account)
                                <tr>
                                    <td class="col-3">{{$account->code}}</td>
                                    <td>{{$account->name}}</td>
                                    <td class="col-3"> 
                                        <div class="d-flex gap-2 justify-content-center"> 
                                            <a class="btn btn-primary" href="{{route('accounts.show', $account->id)}}" role="button">Detalles</a>

                                            <a class="btn btn-warning" href="{{route('accounts.edit', $account->id)}}" role="button">Editar</a>
                                        </div>
                                    </td>
                                </tr> 
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted">
                <a class="btn btn-success w-100" href="{{route('accounts.create')}}" role="button">Añadir</a>
            </div>
        </div>
    </div>
@endsection