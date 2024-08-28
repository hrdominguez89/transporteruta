@extends('adminlte::page')

@section('title', 'Panel de Control')

@section('content_header')
    <h1 class="col-10">¡Bienvenido {{ Auth::user()->name }}!</h1>
@stop

@section('content')
<div class="container col-12">
    <div class="row">
        <div class="col-3">
            <div class="col-12">
                    <div class="col-12 bg-primary text-center p-5">
                        <h2>Clientes Registrados</h2>
                        <h1><b>{{ $clientsCount }}</b></h1>
                        <a href="{{ Route('clients') }}" class="btn btn-secondary">Ir a Clientes</a>                    
                    </div>
                </div>
                <br>
                <div class="col-12">
                    <div class="col-12 bg-info text-center p-5">
                        <h2>Choferes Registrados</h2>
                        <h1><b>{{ $driversCount }}</b></h1>      
                        <a href="{{ Route('drivers') }}" class="btn btn-warning">Ir a Choferes</a>                
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="col-12">
                    <div class="col-12 bg-light text-center p-5">
                        <h2>Facturas Abiertas</h2>
                        <h1><b>{{ $invoicesCount }}</b></h1>
                        <a href="{{ Route('invoices') }}" class="btn btn-success">Ir a Facturas</a>                    
                    </div>
                </div>
                <br>
                <div class="col-12">
                    <div class="col-12 bg-dark text-center p-5">
                        <h2>Recibos Pendientes</h2>
                        <h1><b>{{ $receiptsCount }}</b></h1>      
                        <a href="{{ Route('receipts') }}" class="btn btn-danger">Ir a Recibos</a>                
                </div>
            </div>
        </div>
        <div class="col-6">
            <h4> Clientes con Saldo Deudor</h4>
            <table class="table table-bordered text-center">
                <thead class="bg-danger">
                    <th>Cliente</th>
                    <th>Saldo Deudor</th>
                    <th>Acciones</th>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->balance }}</td>
                            <td>
                                <a href="{{ Route('showClient', $client->id) }}" class="btn btn-info">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('footer')

@stop