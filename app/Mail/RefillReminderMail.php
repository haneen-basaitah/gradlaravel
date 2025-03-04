<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RefillReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $medication;

    public function __construct($medication)
    {
        $this->medication = $medication;
    }

    public function build()
    {
        return $this->subject('🔔 تنبيه: إعادة تعبئة الدواء')
                    ->view('emails.refill_reminder')
                    ->with([
                        'medicationName' => $this->medication->name,
                        'closetNumber' => $this->medication->medicine_closet_location,
                        'cellNumber' => $this->medication->medicine_closet_number,
                        'pillCount' => $this->medication->pill_count,
                    ]);
    }
}
