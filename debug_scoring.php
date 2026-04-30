<?php
use App\Models\Application;
use App\Models\Department;

// Assuming $id is 6 based on previous logs
$id = 6;
$app = Application::find($id);

if (!$app) {
    echo "Application not found\n";
    exit;
}

echo "STUDENT DATA:\n";
echo "Major: " . $app->major . "\n";
echo "Prodi: " . $app->program_studi . "\n";
echo "Keahlian: " . $app->keahlian . "\n";
echo "-------------------\n";

$depts = Department::with(['majors', 'skills'])->get();

foreach ($depts as $dept) {
    echo "DEPT: " . $dept->name . "\n";
    echo "Majors: " . $dept->majors->pluck('name')->implode(', ') . "\n";
    echo "Skills: " . $dept->skills->pluck('name')->implode(', ') . "\n";
    
    // Test match
    $majorMatch = false;
    foreach ($dept->majors as $m) {
        if (stripos($app->major, $m->name) !== false || stripos($app->program_studi, $m->name) !== false) {
            $majorMatch = true;
            echo "  MATCH MAJOR: " . $m->name . "\n";
        }
    }
    
    $matchedSkills = 0;
    foreach ($dept->skills as $s) {
        if (stripos($app->keahlian, $s->name) !== false) {
            $matchedSkills++;
            echo "  MATCH SKILL: " . $s->name . "\n";
        }
    }
    echo "-------------------\n";
}
