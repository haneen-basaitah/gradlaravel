<?php $__env->startSection('content'); ?>
    <div class="container mt-5">
        <h1 class="text-center">Medication Schedule</h1>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo e(route('medications.store')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="row">
                                <!-- Elderly -->
                                <div class="col-md-6">
                                    <label for="patient_id"><strong>Elderly</strong></label>
                                    <select name="patient_id" id="patient_id" class="form-control" required>
                                        <option value="">Select the elderly</option>
                                        <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['patient_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Medicine Closet cell (Drawer Number) -->
                                <div class="col-md-6">
                                    <label for="medicine_closet_number"><strong>Medicine Closet cell</strong></label>
                                    <select name="medicine_closet_number" id="medicine_closet_number" class="form-control"
                                        required>
                                        <option value="">Select Drawer Number</option>
                                        <option value=1>1</option>
                                        <option value=1>2</option>
                                        <option value=1>3</option>
                                    </select>
                                    <?php $__errorArgs = ['medicine_closet_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Medication Name -->
                                <div class="col-md-6 mt-3">
                                    <label for="name"><strong>Medication Name</strong></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Enter medication name" required>
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!--  Closet number -->
                                <div class="col-md-6 mt-3">
                                    <label for="medicine_closet_location"><strong>Medicine Closet Number</strong></label>
                                    <input type="number" name="medicine_closet_location" id="medicine_closet_location"
                                           class="form-control" placeholder="Enter closet location number" min="1" required>
                                    <?php $__errorArgs = ['medicine_closet_location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>


                                <!-- Dosage -->
                                <div class="col-md-6 mt-3">
                                    <label for="dosage"><strong>Doctor Recommended Dosage</strong></label>
                                    <input type="text" name="dosage" id="dosage" class="form-control"
                                        placeholder="Enter dosage" required>
                                    <?php $__errorArgs = ['dosage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Expiration Date -->
                                <div class="col-md-6 mt-3">
                                    <label for="expiration_date"><strong>Expiration Date</strong></label>
                                    <input type="date" name="expiration_date" id="expiration_date" class="form-control"
                                        required>

                                    <?php $__errorArgs = ['expiration_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Time of Intake (12-hour format) -->
                                <div class="col-md-6 mt-3">
                                    <label for="time_of_intake"><strong>Time of Intake</strong></label>
                                    <input type="time" name="time_of_intake" id="time_of_intake" class="form-control"
                                        step="60" required>

                                    <?php $__errorArgs = ['time_of_intake'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <!-- Frequency of Intake -->
                                <div class="col-md-6 mt-3">
                                    <label for="frequency"><strong>Frequency of Intake (Per Day)</strong></label>
                                    <select name="frequency" id="frequency" class="form-control" required>
                                        <option value="">Select Frequency</option>
                                        <option value="1">Once a day</option>
                                        <option value="2">Twice a day</option>
                                        <option value="3">Three times a day</option>
                                        <option value="4">Four times a day</option>
                                    </select>
                                    <?php $__errorArgs = ['frequency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                                <!-- Pill Count -->
                                <div class="col-md-6 mt-3">
                                    <label for="pill_count"><strong>Number of Pills</strong></label>
                                    <input type="number" name="pill_count" id="pill_count" class="form-control"
                                        placeholder="Enter the number of pills" required min="1">
                                    <?php $__errorArgs = ['pill_count'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                            </div>

                            <!-- Submit Button -->
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">Save Medication Information</button>
                                <a href="<?php echo e(route('medications.view')); ?>" class="btn btn-secondary">Cancel</a>
                            </div>

                            <!-- Display Validation Errors -->
                            <?php if($errors->any()): ?>
                                <div class="alert alert-danger mt-3">
                                    <ul>
                                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($error); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('dashboard.layout.app', ['title' => 'Medication Schedule'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\graduations\laravel\medimind\resources\views/dashboard/layout/medications/add.blade.php ENDPATH**/ ?>