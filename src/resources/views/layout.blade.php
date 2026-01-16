@extends('adminlte::page')

@section('css')
    @vite(['resources/css/bootstrap.css'])

    {{-- CSS adicional --}}
    @yield('css_additional')
@stop

@section('content')

    {{-- Contenido principal --}}
    @yield('main')

    {{-- Visualizador de errores de validación --}}
    @include('components.alert-banner')
@stop

@section('js')
    @vite(['resources/js/bootstrap.js', 'resources/js/sweetalert.js'])
    
    {{-- JS adicional --}}
    @yield('js_additional')
@stop
