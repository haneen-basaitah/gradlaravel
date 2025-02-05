@extends('dashboard.layout.app', ['title' => 'Edit Patient'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Edit Patient</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">Patient Information</div>
                <div class="card-body">
                    <form action="{{ route('patients.update', ['id' => $patient->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="form-group mb-3">
                            <label for="name">Patient Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ $patient->name }}" required>
                        </div>

                        <!-- Age -->
                        <div class="form-group mb-3">
                            <label for="age">Age</label>
                            <input type="number" name="age" id="age" class="form-control" value="{{ $patient->age }}" required>
                        </div>

                        <!-- Medical Condition -->
                        <div class="form-group mb-3">
                            <label for="medical_condition">Medical Condition</label>
                            <textarea name="medical_condition" id="medical_condition" class="form-control" rows="3">{{ $patient->medical_condition }}</textarea>
                        </div>

                        <!-- Notes -->
                        <div class="form-group mb-3">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ $patient->notes }}</textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success">Update Patient</button>
                            <a href="{{ route('patients.view') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
