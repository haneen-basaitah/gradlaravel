<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>🚨 تنبيه: جرعة دواء فائتة!</title>
</head>
<body>
    <h2>🚨 تنبيه هام!</h2>
    <p>لقد تم تفويت جرعة الدواء التالية:</p>
    <ul>
        <li><strong>اسم الدواء:</strong> <?php echo e($medicationName); ?></li>
        <li><strong>الوقت:</strong> <?php echo e($time); ?></li>
        <li><strong>الخزانة:</strong> <?php echo e($closet); ?></li>
        <li><strong>الخلية:</strong> <?php echo e($cell); ?></li>
    </ul>
    <p>يرجى التحقق من المريض واتخاذ الإجراءات اللازمة.</p>
</body>
</html>
<?php /**PATH D:\graduations\laravel\medimind\resources\views/emails/missed_dose.blade.php ENDPATH**/ ?>