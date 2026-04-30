<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Application;

$applicationId = 6;
$appRecord = Application::with('members')->find($applicationId);

echo "LEADER: " . $appRecord->major . " | " . $appRecord->keahlian . "\n";
foreach ($appRecord->members as $m) {
    echo "MEMBER: " . $m->major . " | " . $m->keahlian . "\n";
}
