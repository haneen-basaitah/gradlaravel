<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MissedDoseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $medication;
    public $patient;

    public function __construct($medication)
    {
        $this->medication = $medication;

    }

    public function build()
    {
        return $this->subject("🚨 تنبيه: جرعة دواء فائتة!")
                    ->view('emails.missed_dose')
                    ->with([
                        'medicationName' => $this->medication->name,
                        'time' => $this->medication->time_of_intake,
                        'closet' => $this->medication->medicine_closet_location,
                        'cell' => $this->medication->medicine_closet_number,
                    ]);
    }
}
