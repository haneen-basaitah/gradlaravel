@extends('dashboard.layout.app', ['title' => 'Activities'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">📊 تقرير أنشطة الروبوت NAO</h1>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>🧓 اسم المريض</th>
                <th>🎨 نتيجة التمرين اللوني (nao/activity_end)</th>
                <th>🧠 إجابة التمرين المعرفي (nao/answer_report)</th>
                <th>🕒 تاريخ النشاط</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ optional($activity->patient)->name ?? 'غير معروف' }}</td>
                    <td>
                        @if($activity->color_activity_level)
                            <span class="text-success fw-bold">{{ $activity->color_activity_level }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($activity->cognitive_question_answer)
                            <span class="text-primary">{{ $activity->cognitive_question_answer }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">لا توجد أنشطة مسجلة حتى الآن.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
