@extends('dashboard.layout.app', ['title' => 'Add Elderly'])

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-6">
        <h1 class="text-center mb-4">Add New Elderly</h1>
        <div class="card">
            <div class="card-header text-center">Patient Information</div>
            <div class="card-body">
                <form action="{{ route('patients.store') }}" method="POST">
                    @csrf <!-- حماية من هجمات CSRF -->

                    <!-- Name -->
                    <div class="form-group mb-3">
                        <label for="name">Elderly Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter patient name" required>
                    </div>

                    <!-- Age -->
                    <div class="form-group mb-3">
                        <label for="age">Age</label>
                        <input type="number" name="age" id="age" class="form-control" placeholder="Enter patient age" required>
                    </div>

                 <!-- Closet ID -->
                 <div class="form-group mb-3">
                    <label for="closet_id">Closet Number</label>
                    <input type="number" name="closet_id" id="closet_id" class="form-control" placeholder="Enter closet number" required>
                </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Add Elderly</button>
                        <a href="{{ route('patients.view') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
