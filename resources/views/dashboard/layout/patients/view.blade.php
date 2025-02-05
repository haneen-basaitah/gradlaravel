@extends('dashboard.layout.app', ['title' => 'View Patients'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">All Elderly</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>#</th> <!-- الرقم التسلسلي -->
                <th>Name</th>
                <th>Age</th>
                <th>Medical Condition</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($patients as $index => $patient)  <!-- تأكد من وجود foreach -->
                <tr>
                    <td>{{ $loop->iteration }}</td> <!-- الرقم التسلسلي -->
                    <td>{{ $patient->name }}</td> <!-- الاسم -->
                    <td>{{ $patient->age }}</td> <!-- العمر -->
                    <td>{{ $patient->medical_condition }}</td> <!-- الحالة الصحية -->
                    <td>{{ $patient->notes }}</td> <!-- ملاحظات -->
                    <td>
                        <div class="d-flex gap-2">
                            <!-- زر التعديل -->
                            <a href="{{ route('patients.edit', ['id' => $patient->id]) }}" class="btn btn-warning btn-sm">Edit</a>

                            <!-- زر الحذف -->
                            <form action="{{ route('patients.delete', ['id' => $patient->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
