<?php

namespace App\Mail\SharpFleet;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $driverName,
        public string $actorName,
        public string $timezone,
        public string $vehicleOldName,
        public string $vehicleOldReg,
        public string $vehicleNewName,
        public string $vehicleNewReg,
        public Carbon $oldStart,
        public Carbon $oldEnd,
        public Carbon $newStart,
        public Carbon $newEnd,
        public string $event
    ) {
    }

    public function build()
    {
        $subject = match ($this->event) {
            'created' => 'Booking created',
            'cancelled' => 'Booking cancelled',
            default => 'Booking updated',
        };

        return $this->subject($subject)
            ->view('emails.sharpfleet.booking-changed');
    }
}
