@extends('layouts.base')

{{-- Complemento titulo header --}}
@section('title', 'Añadir Cuenta')

{{-- Clases adicionales body --}}
@section('body_class','text-center')

@section('main')
    <div class="container mt-5">
        <div class="card">
            <form action="{{ route('accounts.update', $account->id) }}" method="POST"> 
                <div class="card-header fw-bold fst-italic">Crear Cuenta</div>
                <div class="card-body">
                    
                    {{-- Token de Seguridad --}}
                    @csrf

                    {{-- Inputs:  Codigo, Nombre, Cuenta Padre --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="code" class="form-label">Código</label>
                                <input type="number" class="form-control" name="code" minlength="1" required/>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="name" maxlength="255" required/>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Cuenta Padre</label>
                                <select class="form-select" name="parent_id">
                                    <option value="" selected>Seleccione</option>
                                    
                                    @foreach ($accounts as $account)
                                        <option value="{{$account->id}}">{{$account->code}} - {{$account->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Inputs:  Descripcion --}}
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripcion</label>
                                <input type="text" class="form-control" name="description" maxlength="255"/>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer text-muted d-grid gap-2">
                    <button type="submit" class="btn btn-success">Crear</button>
                    <a class="btn btn-danger" href="{{route("accounts.index")}}" role="button" >Cancelar</a>
                </div>
            </form>
        </div>

        @if (session('notification'))
            {{-- Accede a 'css-class' para el color y 'text' para el mensaje --}}
            <div class="alert alert-{{ session('notification.css-class') }} mt-2" role="alert">
                {{ session('notification.text') }}
            </div>
        @endif
    </div>
@endsection