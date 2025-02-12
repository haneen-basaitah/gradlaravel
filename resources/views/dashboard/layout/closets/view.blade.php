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

<script>
    // تحديث البيانات بدون إعادة تحميل الصفحة
    setInterval(function() {
        fetch("{{ route('closets.subscribe') }}")
            .then(response => response.text())
            .then(html => {
                document.getElementById("closet-data").innerHTML = new DOMParser().parseFromString(html, 'text/html').querySelector("#closet-data").innerHTML;
            });
    }, 5000); // تحديث البيانات كل 5 ثوانٍ
</script>

@endsection
