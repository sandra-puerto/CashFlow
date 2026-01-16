@extends('layout')

@section('title', 'Ultimas Transacciones')

@section('content_header')
    <div class="text-center">
        <h1>Ultimas Transacciones</h1>
    </div>
@stop

@section('main')

    <a class="btn btn-primary" href="{{ route('web.transactions.create') }}" role="button" >Crear</a>

@stop