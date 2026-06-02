<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$meeting = new \App\Models\Meeting();
$meeting->fill([
    'date' => '2026-06-15',
    'start_time' => '10:00',
    'end_time' => '11:00'
]);

try {
    $date = $meeting->date;
    echo "Class: " . get_class($date) . "\n";
    echo "Format: " . $date->format('Y-m-d') . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
