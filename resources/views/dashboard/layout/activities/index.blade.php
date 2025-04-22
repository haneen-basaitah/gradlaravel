
@extends('dashboard.layout.app', ['title' => 'Activities'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Activities Status</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Elderly Name</th>
                <th>Color Sequence Activity Level</th>
                <th>Cognitive Question Activity Answer</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ optional($activity->patient)->name ?? 'غير معروف' }}</td>
                    <td>{{ $activity->color_activity_level ?? '-' }}</td>
                    <td>{{ $activity->cognitive_question_answer ?? '-' }}</td>
                    <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">لا توجد أنشطة مسجلة حتى الآن.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
