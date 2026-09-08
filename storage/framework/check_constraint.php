<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$constraint = DB::table('information_schema.check_constraints')
    ->where('constraint_name', 'horario_dia_semana_check')
    ->first();

if ($constraint) {
    echo "Check constraint: " . $constraint->check_clause . PHP_EOL;
} else {
    echo "No se encontró check constraint 'horario_dia_semana_check'" . PHP_EOL;
}

echo PHP_EOL . "Valores actuales de dia_semana en horario:" . PHP_EOL;
foreach (DB::table('horario')->select('dia_semana')->distinct()->get() as $r) {
    echo "  - " . $r->dia_semana . PHP_EOL;
}