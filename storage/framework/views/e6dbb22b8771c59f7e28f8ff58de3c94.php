<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - MediMind Manager</title>

    <!-- Link to CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('front/assets/css/style.css')); ?>">

    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div id="app">
        <div class="navbar-container">
            <nav class="navbar navbar-expand-lg navbar-light">
                <div class="container">
                    <div class="navbar-logo">
                        <img src="<?php echo e(asset('front/assets/img/logo.png')); ?>" alt="MediMind Logo">
                        <span>Medi<span class="highlight">Mind</span></span>
                    </div>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/')); ?>">Home</a></li>

                        <?php if(auth()->guard()->check()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/dashboard-panel')); ?>">Dashboard</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('login')); ?>">Login</a></li>

                            <?php if(Route::has('register')): ?>
                                <li class="nav-item"><a class="nav-link" href="<?php echo e(route('register')); ?>">Signup</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="home-wrapper">
        <div class="content-box">
            <img src="<?php echo e(asset('front/assets/img/logo.png')); ?>" alt="MediMind Logo">
            <div>
                <h1>Welcome to <span>Medi<span class="highlight">Mind</span></span> Manager!</h1>
                <p>
                    We are here to help elderly individuals and caregivers manage medication schedules
                    and memory activities with ease.
                    <strong><span>Medi<span class="highlight">Mind</span></span> Manager</strong> combines smart tools to ensure timely reminders for medications
                    and organizes cognitive activities.
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php /**PATH D:\graduations\laravel\medimind\resources\views///frontend/index.blade.php ENDPATH**/ ?>