@extends('dashboard.layout.app', ['title' => 'Closet Status'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Closet Status</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Temperature (°C)</th>
                <th>Humidity (%)</th>
                <th>Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($closets as $closet)
                <tr>
                    <td>{{ $closet->id }}</td>
                    <td>{{ $closet->temperature }} °C</td>
                    <td>{{ $closet->humidity }} %</td>
                    <td>{{ $closet->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
