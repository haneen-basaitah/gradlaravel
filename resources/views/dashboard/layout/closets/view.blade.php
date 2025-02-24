@extends('dashboard.layout.app', ['title' => 'Closet Status'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Closet Status</h1>

    @if($closets->count() > 0)
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>🌡️ Temperature (°C)</th>
                    <th>💧 Humidity (%)</th>
                    <th>🕒 Last Updated</th>
                </tr>
            </thead>
            <tbody id="closet-data">
                @foreach($closets as $closet)
                    <tr>
                        <td>{{ $closet->id }}</td>
                        <td>{{ $closet->temperature ? $closet->temperature . ' °C' : '❌ No Data' }}</td>
                        <td>{{ $closet->humidity ? $closet->humidity . ' %' : '❌ No Data' }}</td>
                        <td>{{ $closet->updated_at ? $closet->updated_at->format('Y-m-d H:i:s') : '❌ Not Updated' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-warning text-center">
            ❌ No closet data available.
        </div>
    @endif
</div>
@endsection
