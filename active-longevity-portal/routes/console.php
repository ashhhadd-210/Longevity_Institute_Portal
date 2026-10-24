<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| An invitation is unusable the moment its expiry passes, whether or not
| anything has said so. This sweep exists so that the Institute is shown the
| truth: a lapsed invitation should stop counting as outstanding, and a
| candidate's history should be able to say that one was never taken up.
|
| Daily is frequent enough. Nothing depends on the exact moment, because
| acceptance checks the date itself rather than trusting the status.
*/
Schedule::command('portal:expire-invitations')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping();
