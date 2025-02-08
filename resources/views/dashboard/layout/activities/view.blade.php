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
            </tr>
        </thead>
        <tbody>
            
            @foreach($activities as $activity)
                <tr>
                    <td>{{ $activity->patient->name }}</td>
                    <td>{{ $activity->color_activity_level }}</td>
                    <td>{{ $activity->cognitive_question_answer }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
