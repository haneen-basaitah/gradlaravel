<?php $__env->startSection('content'); ?>
<div class="container mt-5 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 700px; background-color: #6d8d79; color: white;">
        <div class="card-body text-center p-5">
            <img src="<?php echo e(asset('dashboard/dist/img/logo.png')); ?>" alt="MediMind Logo" style="width: 100px; height: auto;" class="mb-4">
            <h2 class="fw-bold">
                Welcome to <span style="color: #ffffff;">Medi</span><span style="color: #6ee7b7;">Mind</span> Manager!
            </h2>
            <p class="mt-3 fs-5">
                We are here to help elderly individuals and caregivers manage medication schedules and memory activities with ease.
            </p>
            <p class="fs-6">
                <strong><span style="color: #ffffff;">Medi</span><span style="color: #6ee7b7;">Mind</span> Manager</strong> combines smart tools to ensure timely reminders for medications and organizes cognitive activities.
            </p>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layout.app', ['title' => 'Dashboard'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\graduations\laravel\medimind\resources\views/dashboard/index.blade.php ENDPATH**/ ?>