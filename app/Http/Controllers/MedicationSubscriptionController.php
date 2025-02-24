<?php

namespace App\Http\Controllers;
use App\Services\MqttClientService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class MedicationSubscriptionController extends Controller
{
    public function subscribeToMissedDoses()
    {
        $mqtt = new MqttClientService();
        $mqtt->connect();

        if ($mqtt->isConnected()) {
            Log::info("📡 الاشتراك في التوبيك: medication/missed");

            $mqtt->subscribe("medication/missed", function ($receivedTopic, $message) {
                Log::info("📩 رسالة مستقبلة من ESP32:");
                Log::info("📝 التوبيك: $receivedTopic");
                Log::info("📨 البيانات الأصلية: " . $message);

                // ✅ تحويل JSON إلى Array والتحقق من صحته
                $data = json_decode($message, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($data["status"], $data["closet_id"], $data["cell_id"])) {
                    Log::info("✅ الجرعة فائتة!");
                    Log::info("🏠 رقم الخزانة: " . $data["closet_id"]);
                    Log::info("📦 رقم الخلية: " . $data["cell_id"]);
                } else {
                    Log::error("🔴 البيانات المستقبلة غير صحيحة أو JSON غير صالح: " . json_last_error_msg());
                }
            });
        } else {
            Log::error("🔴 فشل الاتصال بـ MQTT، سيتم إعادة المحاولة بعد 5 ثوانٍ...");
            sleep(5);
            $this->subscribeToMissedDoses();
        }
    }

    // send email to the cargivers
    private function sendMissedDoseEmail($closet_id, $cell_id)
    {
        // الحصول على البريد الإلكتروني لمقدم الرعاية المسجل حاليًا
        $caregiverEmail = Auth::user()->email;

        if ($caregiverEmail) {
            $subject = '⚠️ جرعة دواء فائتة!';
            $message = "تم تفويت جرعة الدواء في الخزانة: $closet_id الخلية: $cell_id.";

            Mail::raw($message, function ($mail) use ($caregiverEmail, $subject) {
                $mail->to($caregiverEmail)->subject($subject);
            });

            Log::info("📧 تم إرسال إشعار عبر البريد الإلكتروني إلى: $caregiverEmail");
        } else {
            Log::error("🔴 لم يتم العثور على بريد إلكتروني لمقدم الرعاية.");
        }
    }

}
