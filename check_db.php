<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Department;
use App\Models\Application;

$applicationId = 6;
$student = Application::find($applicationId);

echo "STUDENT: " . $student->major . " | " . $student->program_studi . "\n";
echo "SKILLS: " . $student->keahlian . "\n\n";

$depts = Department::with(['majors', 'skills'])->get();
foreach ($depts as $d) {
    echo "DEPT: " . $d->name . "\n";
    echo "  RELEVANT MAJORS: " . $d->majors->pluck('name')->implode(', ') . "\n";
    echo "  SKILLS: " . $d->skills->pluck('name')->implode(', ') . "\n";
}
