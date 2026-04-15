<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule auto-resend of pending invitations daily
Schedule::command('invitations:resend-pending')->daily();

// Schedule approval deadline reminders hourly
Schedule::command('approvals:send-deadline-reminders')->hourly();
