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
                    <th>Time of Intake</th>
                    <th>Cell Number</th>
                    <th>Closet Number</th>
                    <th>Expiration Date</th>
                    <th>Pill Count</th>
                    <th>Status</th>
                    <th>Actions</th> <!-- زر التعديل -->
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $medications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medication): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($loop->iteration); ?></td>
                        <td><?php echo e($medication->patient->name ?? 'Unknown'); ?></td> <!-- جلب اسم المريض -->
                        <td><?php echo e($medication->name); ?></td>
                        <td><?php echo e($medication->time_of_intake); ?></td>
                        <td><?php echo e($medication->medicine_closet_number); ?></td>
                        <td><?php echo e($medication->medicine_closet_location); ?></td>
                        <td><?php echo e($medication->expiration_date); ?></td>
                        <td><?php echo e($medication->pill_count); ?></td>
                        <td><?php echo e($medication->status); ?></td>
                        <td class="d-flex gap-2 align-items-center">
                            <!-- زر Edit -->
                            <button class="btn btn-primary btn-sm me-2" data-toggle="modal"
                                data-target="#editPillCountModal<?php echo e($medication->id); ?>">
                                Edit
                            </button>

                            <!-- زر Delete -->
                            <form action="<?php echo e(route('medications.destroy', $medication->id)); ?>" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this medication?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>

                    </tr>

                    <!-- ✅ نموذج تعديل pill_count -->
                    <div class="modal fade" id="editPillCountModal<?php echo e($medication->id); ?>" tabindex="-1" role="dialog"
                        aria-labelledby="editPillCountLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editPillCountLabel">Edit Pill Count</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="<?php echo e(route('medications.updatePillCount', $medication->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <div class="modal-body">
                                        <label for="pill_count">New Pill Count:</label>
                                        <input type="number" name="pill_count" class="form-control"
                                            value="<?php echo e($medication->pill_count); ?>" required min="0">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-success">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layout.app', ['title' => 'Medications List'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\graduations\laravel\medimind\resources\views/dashboard/layout/medications/view.blade.php ENDPATH**/ ?>