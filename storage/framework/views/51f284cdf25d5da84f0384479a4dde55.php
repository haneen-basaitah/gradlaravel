<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <h1 class="text-center">تقرير أنشطة الروبوت NAO</h1>

    <!-- 🔵 زر اختيار المريض وزر الطباعة -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <select id="patientFilter" class="form-select">
                <option value="">🔎 اختر مريضًا لعرض أنشطته</option>
                <?php
                    $patients = $activities->filter(fn($a) => $a->patient)->pluck('patient')->unique('id');
                ?>
                <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div>
            <button onclick="window.print();" class="btn btn-primary">
                طباعة التقرير
            </button>
        </div>
    </div>

    <!-- ✅ تجميع الأنشطة حسب التاريخ -->
    <?php
        $grouped = $activities->groupBy(function($a) {
            return \Carbon\Carbon::parse($a->created_at)->format('Y-m-d');
        });
    ?>

    <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $groupActivities): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="mb-3">
            <h4 class="text-center text-white bg-dark p-2 rounded">📅 الأنشطة بتاريخ: <?php echo e($date); ?></h4>
            <div class="row">
                <?php $__currentLoopData = $groupActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 mb-4 patient-card" data-patient-id="<?php echo e(optional($activity->patient)->id); ?>">
                        <div class="card border-primary shadow rounded-4">
                            <div class="card-header bg-primary text-white fw-bold">
                                👤 <?php echo e(optional($activity->patient)->name ?? 'غير معروف'); ?>

                            </div>
                            <div class="card-body">
                                <?php if($activity->color_activity_level): ?>
                                    <p class="mb-1">🎯 <strong>Color Activity:</strong>
                                        <span class="text-success"><?php echo e($activity->color_activity_level); ?></span>
                                    </p>
                                <?php endif; ?>

                                <?php if($activity->cognitive_question_answer): ?>
                                    <p class="mb-1">🧠 <strong>Cognitive Answer:</strong>
                                        <span class="text-primary"><?php echo e($activity->cognitive_question_answer); ?></span>
                                    </p>
                                <?php endif; ?>

                                <p class="mb-1">📅 <strong>Date:</strong>
                                    <?php echo e($activity->created_at->format('Y-m-d H:i')); ?>

                                </p>
                                <p class="mb-1">💊 <strong>Medication Time:</strong>
                                    <?php echo e($activity->medication_time ?? '-'); ?>

                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-center text-muted">
            لا توجد أنشطة مسجلة حتى الآن.
        </div>
    <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layout.app', ['title' => 'Activities'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\graduations\laravel\medimind\resources\views/dashboard/layout/activities/view.blade.php ENDPATH**/ ?>