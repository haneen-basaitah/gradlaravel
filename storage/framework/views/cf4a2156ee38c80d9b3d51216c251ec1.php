<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔔 تنبيه: إعادة تعبئة الدواء</title>
</head>
<body>
    <h2>🔔 تنبيه: إعادة تعبئة الدواء</h2>
    <p>📢 عزيزي الـ Caregiver،</p>
    <p>تم تقليل عدد الحبات المتبقية من الدواء <strong><?php echo e($medicationName); ?></strong> إلى <strong><?php echo e($pillCount); ?></strong> فقط.</p>
    <p>🔹 <strong>الخزانة:</strong> <?php echo e($closetNumber); ?></p>
    <p>🔹 <strong>الخلية:</strong> <?php echo e($cellNumber); ?></p>
    <p>يرجى إعادة التعبئة في أقرب وقت ممكن لضمان استمرار العلاج.</p>
    <br>
    <p>شكرًا لاهتمامك! 💙</p>
</body>
</html>
<?php /**PATH D:\graduations\laravel\medimind\resources\views/emails/refill_reminder.blade.php ENDPATH**/ ?>