<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Sky\Calendar;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->command('queue:work --daemon --tries=100')->everyMinute()->withoutOverlapping();

        $schedule->call(function () {
            $events = Calendar::with('admins')->whereNotNull('date')->whereDate('date', '<=', Carbon::now())->get();
            foreach ($events as $event) {
                \Mail::raw($event->description, function ($m) use ($event) {
                    $m->from(\Config::get('mail.from.address'));
                    $m->sender(\Config::get('mail.from.address'));
                    $m->replyTo(\Config::get('mail.from.address'));
                    $m->to($event->admins->pluck('email')->toArray());
                    $m->subject($event->date);
                });

                $event->date = Carbon::parse($event->date, 'Europe/Sofia')->addYear(1);
                $event->save();
            }
        })->daily();
    }
}
