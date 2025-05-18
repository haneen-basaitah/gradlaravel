@extends('dashboard.layout.app', ['title' => 'Activities'])

@section('content')
<div class="container mt-5">
    <h1 class="text-center">تقرير أنشطة الروبوت NAO</h1>

    <!-- 🔵 زر اختيار المريض وزر الطباعة -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <select id="patientFilter" class="form-select">
                <option value="">🔎 اختر مريضًا لعرض أنشطته</option>
                @php
                    $patients = $activities->filter(fn($a) => $a->patient)->pluck('patient')->unique('id');
                @endphp
                @foreach($patients as $patient)
                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button onclick="window.print();" class="btn btn-primary">
                طباعة التقرير
            </button>
        </div>
    </div>

    <!-- ✅ تجميع الأنشطة حسب التاريخ -->
    @php
        $grouped = $activities->groupBy(function($a) {
            return \Carbon\Carbon::parse($a->created_at)->format('Y-m-d');
        });
    @endphp

    @forelse($grouped as $date => $groupActivities)
        <div class="mb-3">
            <h4 class="text-center text-white bg-dark p-2 rounded">📅 الأنشطة بتاريخ: {{ $date }}</h4>
            <div class="row">
                @foreach($groupActivities as $activity)
                    <div class="col-md-6 mb-4 patient-card" data-patient-id="{{ optional($activity->patient)->id }}">
                        <div class="card border-primary shadow rounded-4">
                            <div class="card-header bg-primary text-white fw-bold">
                                👤 {{ optional($activity->patient)->name ?? 'غير معروف' }}
                            </div>
                            <div class="card-body">
                                @if($activity->color_activity_level)
                                    <p class="mb-1">🎯 <strong>Color Activity:</strong>
                                        <span class="text-success">{{ $activity->color_activity_level }}</span>
                                    </p>
                                @endif

                                @if($activity->cognitive_question_answer)
                                    <p class="mb-1">🧠 <strong>Cognitive Answer:</strong>
                                        <span class="text-primary">{{ $activity->cognitive_question_answer }}</span>
                                    </p>
                                @endif

                                <p class="mb-1">📅 <strong>Date:</strong>
                                    {{ $activity->created_at->format('Y-m-d H:i') }}
                                </p>
                                <p class="mb-1">💊 <strong>Medication Time:</strong>
                                    {{ $activity->medication_time ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted">
            لا توجد أنشطة مسجلة حتى الآن.
        </div>
    @endforelse
</div>

<!-- 🔵 سكربت فلترة المرضى -->
<script>
    document.getElementById('patientFilter').addEventListener('change', function () {
        var selectedId = this.value;
        var cards = document.querySelectorAll('.patient-card');

        cards.forEach(function (card) {
            if (selectedId === '' || card.getAttribute('data-patient-id') === selectedId) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>

<!-- 🔵 ستايل خاص للطباعة -->
<style>
@media print {
    #patientFilter,
    button,
    .form-select {
        display: none !important;
    }

    .card {
        page-break-inside: avoid;
        border: 1px solid black !important;
    }

    .card-header {
        background-color: #000 !important;
        color: #fff !important;
    }

    body {
        background-color: white !important;
    }
}
</style>
@endsection
