@extends('dashboard.layout.app', ['title' => 'Add Activity'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Add Activity</h1>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('activities.store') }}" method="POST">
                        @csrf
                        <!-- اختيار المريض -->
                        <div class="form-group mb-3">
                            <label for="patient_id">Select Patient</label>
                            <select name="patient_id" id="patient_id" class="form-control" required>
                                <option value="">-- Select Patient --</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- وقت نشاط الروبوت -->
                        <div class="form-group mb-3">
                            <label for="time_of_robot_activity">Time of Robot Activity</label>
                            <input type="time" name="time_of_robot_activity" id="time_of_robot_activity" class="form-control" required>
                            @error('time_of_robot_activity')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- وقت نشاط الألوان -->
                        <div class="form-group mb-3">
                            <label for="time_of_color_activity">Time of Color Activity</label>
                            <input type="time" name="time_of_color_activity" id="time_of_color_activity" class="form-control" required>
                            @error('time_of_color_activity')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Save Activity</button>
                            <a href="{{ route('activities.view') }}" class="btn btn-secondary">Cancel</a>
                        </div>

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
