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

                        <!-- Closet ID -->
                        <div class="form-group mb-3">
                            <label for="closet_id">Closet Number</label>
                            <input type="number" name="closet_id" id="closet_id" class="form-control" value="{{ $patient->closet_id }}" required>
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
