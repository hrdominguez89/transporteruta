@extends('adminlte::page')

@section('title', 'Liquidaciones')

@section('content_header')
    <div class="row align-items-center">
        <h1 class="col-10">Liquidaciones de sueldo</h1>
        <div class="col-2 text-right">
           <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaLiquidacion">
    <i class="fas fa-plus"></i> Nueva liquidación
</button>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-sm table-bordered text-center data-table ">
                <thead >
                    <tr class="bg-danger">
                        <th>ID</th>
                        <th>Chofer</th>
                        <th>Período</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settlements as $settlement)
                        <tr>
                            <td>{{ $settlement->id }}</td>
                            <td>{{ $settlement->driver->name }}</td>
                            <td>{{ $settlement->periodo->format('m/Y') }}</td>
                            <td>{{ $settlement->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('Settlements.show', $settlement) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalNuevaLiquidacion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('Settlements.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva liquidación</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="driver_id">Chofer</label>
                        <select name="driver_id" id="driver_id" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="periodo">Período</label>
                        <input type="month" name="periodo" id="periodo" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
    <script>
        $(document).ready(function () {
            $('.data-table').DataTable({
                order: [[2, 'desc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' }
            });
        });
    </script>
@stop