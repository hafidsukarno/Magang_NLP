use Illuminate\Console\Scheduling\Schedule;
// ...

protected function schedule(Schedule $schedule)
{
    // existing schedules...

    // Tandai aplikasi yang sudah lewat periode menjadi 'selesai' setiap hari jam 00:05
    $schedule->command('applications:mark-completed')->dailyAt('00:05');
}
