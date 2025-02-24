@extends('dashboard.layout.app', ['title' => 'Medication Schedule'])

@section('content')
    <div class="container mt-5">
        <h1 class="text-center">Medication Schedule</h1>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('medications.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <!-- Elderly -->
                                <div class="col-md-6">
                                    <label for="patient_id"><strong>Elderly</strong></label>
                                    <select name="patient_id" id="patient_id" class="form-control" required>
                                        <option value="">Select the elderly</option>
                                        @foreach ($patients as $patient)
                                            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Medicine Closet cell (Drawer Number) -->
                                <div class="col-md-6">
                                    <label for="medicine_closet_number"><strong>Medicine Closet cell</strong></label>
                                    <select name="medicine_closet_number" id="medicine_closet_number" class="form-control"
                                        required>
                                        <option value="">Select Drawer Number</option>
                                        <option value=1>1</option>
                                        <option value=1>2</option>
                                        <option value=1>3</option>
                                    </select>
                                    @error('medicine_closet_number')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Medication Name -->
                                <div class="col-md-6 mt-3">
                                    <label for="name"><strong>Medication Name</strong></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Enter medication name" required>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!--  Closet number -->
                                <div class="col-md-6 mt-3">
                                    <label for="medicine_closet_location"><strong>Medicine Closet Number</strong></label>
                                    <input type="number" name="medicine_closet_location" id="medicine_closet_location"
                                           class="form-control" placeholder="Enter closet location number" min="1" required>
                                    @error('medicine_closet_location')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>


                                <!-- Dosage -->
                                <div class="col-md-6 mt-3">
                                    <label for="dosage"><strong>Doctor Recommended Dosage</strong></label>
                                    <input type="text" name="dosage" id="dosage" class="form-control"
                                        placeholder="Enter dosage" required>
                                    @error('dosage')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Expiration Date -->
                                <div class="col-md-6 mt-3">
                                    <label for="expiration_date"><strong>Expiration Date</strong></label>
                                    <input type="date" name="expiration_date" id="expiration_date" class="form-control"
                                        required>

                                    @error('expiration_date')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Time of Intake (12-hour format) -->
                                <div class="col-md-6 mt-3">
                                    <label for="time_of_intake"><strong>Time of Intake</strong></label>
                                    <input type="time" name="time_of_intake" id="time_of_intake" class="form-control"
                                        step="60" required>

                                    @error('time_of_intake')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Frequency of Intake -->
                                <div class="col-md-6 mt-3">
                                    <label for="frequency"><strong>Frequency of Intake (Per Day)</strong></label>
                                    <select name="frequency" id="frequency" class="form-control" required>
                                        <option value="">Select Frequency</option>
                                        <option value="1">Once a day</option>
                                        <option value="2">Twice a day</option>
                                        <option value="3">Three times a day</option>
                                        <option value="4">Four times a day</option>
                                    </select>
                                    @error('frequency')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Pill Count -->
                                <div class="col-md-6 mt-3">
                                    <label for="pill_count"><strong>Number of Pills</strong></label>
                                    <input type="number" name="pill_count" id="pill_count" class="form-control"
                                        placeholder="Enter the number of pills" required min="1">
                                    @error('pill_count')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <!-- Submit Button -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">Save Medication Information</button>
                                <a href="{{ route('medications.view') }}" class="btn btn-secondary">Cancel</a>
                            </div>

                            <!-- Display Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
