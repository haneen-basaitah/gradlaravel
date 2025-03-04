<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <h1 class="text-center">Medication Schedule</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <table class="table table-striped table-hover mt-3">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Elderly</th> <!-- اسم المريض -->
                <th>Medication Name</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Time of Intake</th>
                <th>Closet Number</th>
                <th>Location</th>
                <th>Expiration Date</th>
                <th>pill_count</th>


            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $medications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medication): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($medication->patient->name ?? 'Unknown'); ?></td> <!-- جلب اسم المريض -->
                    <td><?php echo e($medication->name); ?></td>
                    <td><?php echo e($medication->dosage); ?></td>
                    <td><?php echo e($medication->frequency); ?></td>
                    <td><?php echo e($medication->time_of_intake); ?></td>
                    <td><?php echo e($medication->medicine_closet_number); ?></td>
                    <td><?php echo e($medication->medicine_closet_location); ?></td>
                    <td><?php echo e($medication->expiration_date); ?></td>
                    <td><?php echo e($medication->pill_count); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layout.app', ['title' => 'Medications List'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\graduations\laravel\medimind\resources\views/dashboard/layout/medications/view.blade.php ENDPATH**/ ?>