<?php

namespace App\Mail\SharpFleet;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $driverName,
        public string $timezone,
        public Carbon $start,
        public Carbon $end,
        public string $vehicleName,
        public string $vehicleReg,
        public string $customerName,
        public string $notes
    ) {
    }

    public function build()
    {
        $subject = 'Booking reminder';

        try {
            $subject .= ' - ' . $this->start->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            // ignore formatting errors
        }

        return $this->subject($subject)
            ->view('emails.sharpfleet.booking-reminder');
    }
}
