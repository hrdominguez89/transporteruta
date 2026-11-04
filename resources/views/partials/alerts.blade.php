@php
    $flashAlerts = [
        'success' => 'alert-success',
        'warning' => 'alert-warning',
        'error'   => 'alert-danger',
        'info'    => 'alert-info',
    ];
@endphp

@foreach ($flashAlerts as $key => $class)
    @if (session($key))
        <div id="flash-{{ $key }}" class="alert {{ $class }} alert-dismissible fade show w-100" role="alert">
            {{ session($key) }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <script>
            setTimeout(function () { $('#flash-{{ $key }}').alert('close'); }, 5000);
        </script>
    @endif
@endforeach
