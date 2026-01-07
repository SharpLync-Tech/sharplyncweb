<?php

namespace Tests\Unit\SharpFleet;

use App\Mail\SharpFleet\BookingChanged;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class BookingChangedMailTest extends TestCase
{
    public function test_subject_for_created_event(): void
    {
        $m = new BookingChanged(
            driverName: 'Driver',
            actorName: 'Admin',
            timezone: 'Australia/Sydney',
            vehicleOldName: '',
            vehicleOldReg: '',
            vehicleNewName: 'Vehicle',
            vehicleNewReg: 'ABC-123',
            oldStart: Carbon::parse('2026-01-08 10:00:00', 'UTC'),
            oldEnd: Carbon::parse('2026-01-08 11:00:00', 'UTC'),
            newStart: Carbon::parse('2026-01-08 10:00:00', 'UTC'),
            newEnd: Carbon::parse('2026-01-08 11:00:00', 'UTC'),
            event: 'created'
        );

        $built = $m->build();
        $this->assertSame('Booking created', $built->subject);
    }

    public function test_subject_for_cancelled_event(): void
    {
        $m = new BookingChanged(
            driverName: 'Driver',
            actorName: 'Admin',
            timezone: 'Australia/Sydney',
            vehicleOldName: 'Vehicle',
            vehicleOldReg: 'ABC-123',
            vehicleNewName: 'Vehicle',
            vehicleNewReg: 'ABC-123',
            oldStart: Carbon::parse('2026-01-08 10:00:00', 'UTC'),
            oldEnd: Carbon::parse('2026-01-08 11:00:00', 'UTC'),
            newStart: Carbon::parse('2026-01-08 10:00:00', 'UTC'),
            newEnd: Carbon::parse('2026-01-08 11:00:00', 'UTC'),
            event: 'cancelled'
        );

        $built = $m->build();
        $this->assertSame('Booking cancelled', $built->subject);
    }

    public function test_subject_for_updated_event_default(): void
    {
        $m = new BookingChanged(
            driverName: 'Driver',
            actorName: 'Admin',
            timezone: 'Australia/Sydney',
            vehicleOldName: 'Old',
            vehicleOldReg: 'OLD-1',
            vehicleNewName: 'New',
            vehicleNewReg: 'NEW-1',
            oldStart: Carbon::parse('2026-01-08 10:00:00', 'UTC'),
            oldEnd: Carbon::parse('2026-01-08 11:00:00', 'UTC'),
            newStart: Carbon::parse('2026-01-08 12:00:00', 'UTC'),
            newEnd: Carbon::parse('2026-01-08 13:00:00', 'UTC'),
            event: 'updated'
        );

        $built = $m->build();
        $this->assertSame('Booking updated', $built->subject);
    }
}
