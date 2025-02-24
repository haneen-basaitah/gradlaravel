@extends('dashboard.layout.app', ['title' => 'Medications List'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Medication Schedule</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-striped table-hover mt-3">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Elderly</th> <!-- اسم المريض -->
                <th>Medication Name</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Time of Intake</th>
                <th>Closet Number</th>
                <th>Location</th>
                <th>Expiration Date</th>
                <th>pill_count</th>


            </tr>
        </thead>
        <tbody>
            @foreach ($medications as $medication)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $medication->patient->name ?? 'Unknown' }}</td> <!-- جلب اسم المريض -->
                    <td>{{ $medication->name }}</td>
                    <td>{{ $medication->dosage }}</td>
                    <td>{{ $medication->frequency }}</td>
                    <td>{{ $medication->time_of_intake }}</td>
                    <td>{{ $medication->medicine_closet_number }}</td>
                    <td>{{ $medication->medicine_closet_location }}</td>
                    <td>{{ $medication->expiration_date }}</td>
                    <td>{{ $medication->pill_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
