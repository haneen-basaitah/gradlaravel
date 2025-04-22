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
                <th>Pill Count</th>
                <th>Actions</th> <!-- زر التعديل -->
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
                    <td>
                        <!-- زر فتح النموذج -->
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editPillCountModal{{ $medication->id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                <!-- ✅ نموذج تعديل pill_count -->
                <div class="modal fade" id="editPillCountModal{{ $medication->id }}" tabindex="-1" role="dialog" aria-labelledby="editPillCountLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editPillCountLabel">Edit Pill Count</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('medications.updatePillCount', $medication->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <label for="pill_count">New Pill Count:</label>
                                    <input type="number" name="pill_count" class="form-control" value="{{ $medication->pill_count }}" required min="0">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            @endforeach
        </tbody>
    </table>
</div>
@endsection
