<?php

// Activa WAL sobre la base de datos dedicada de pruebas E2E (dusk.sqlite).
// WAL reduce drásticamente el coste de fsync por INSERT en Windows.

$ruta = __DIR__ . '/../database/dusk.sqlite';

$db = new SQLite3($ruta);
$db->exec('PRAGMA journal_mode=WAL');
$db->exec('PRAGMA synchronous=NORMAL');

echo 'journal_mode: ' . $db->querySingle('PRAGMA journal_mode') . PHP_EOL;
echo 'synchronous: ' . $db->querySingle('PRAGMA synchronous') . PHP_EOL;
