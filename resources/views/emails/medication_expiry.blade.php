<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تنبيه بانتهاء صلاحية الدواء</title>
</head>
<body style="font-family: Arial, sans-serif; direction: rtl; text-align: right;">
    <h2>🔔 تنبيه هام!</h2>

    <p>مرحبًا،</p>

    <p>نود إعلامك بأن الدواء التالي سينتهي خلال <strong>10 أيام</strong>:</p>

    <ul>
        <li><strong>اسم الدواء:</strong> {{ $medication->medication_name }}</li>
        <li><strong>تاريخ انتهاء الصلاحية:</strong> {{ $medication->expiration_date }}</li>
        <li><strong>اسم المريض:</strong> {{ $medication->patient->name ?? 'غير معروف' }}</li>
    </ul>

    <p>يرجى التأكد من تجديد أو استبدال هذا الدواء قبل انتهاء الصلاحية لتجنب أي مشاكل.</p>

    <p>مع تحياتنا،<br> فريق MediMind</p>
</body>
</html>
