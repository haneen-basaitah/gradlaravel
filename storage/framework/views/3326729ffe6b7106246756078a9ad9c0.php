<?php $__env->startSection('content'); ?>
<div class="container mt-5">
    <h1 class="text-center">All Elderly</h1>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

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
            <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>  <!-- تأكد من وجود foreach -->
                <tr>
                    <td><?php echo e($loop->iteration); ?></td> <!-- الرقم التسلسلي -->
                    <td><?php echo e($patient->name); ?></td> <!-- الاسم -->
                    <td><?php echo e($patient->age); ?></td> <!-- العمر -->
                    <td><?php echo e($patient->medical_condition); ?></td> <!-- الحالة الصحية -->
                    <td><?php echo e($patient->notes); ?></td> <!-- ملاحظات -->
                    <td>
                        <div class="d-flex gap-2">
                            <!-- زر التعديل -->
                            <a href="<?php echo e(route('patients.edit', ['id' => $patient->id])); ?>" class="btn btn-warning btn-sm">Edit</a>

                            <!-- زر الحذف -->
                            <form action="<?php echo e(route('patients.delete', ['id' => $patient->id])); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layout.app', ['title' => 'View Patients'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\graduations\laravel\medimind\resources\views/dashboard/layout/patients/view.blade.php ENDPATH**/ ?>