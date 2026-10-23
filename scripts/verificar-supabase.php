<?php

// VERIFICACIÓN DE SOLO LECTURA del estado de la BD de Supabase.
// No ejecuta NINGUNA escritura. Solo cuenta tablas del esquema public.

$dsn = 'pgsql:host=aws-0-us-east-2.pooler.supabase.com;port=6543;dbname=postgres;connect_timeout=10';

try {
    $pdo = new PDO($dsn, 'postgres.mczmhfthneqjfkdatjpg', 'rmlpEGkMjL3bTXri', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);

    $tablas = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);

    echo "Conexion OK.\n";
    echo "Tablas en public: " . count($tablas) . "\n";
    foreach ($tablas as $t) {
        echo " - $t\n";
    }

    // Referencias rápidas para saber si hay datos del colegio
    foreach (['usuario', 'alumno', 'aula', 'matricula'] as $posible) {
        if (in_array($posible, $tablas, true)) {
            $n = $pdo->query("SELECT count(*) FROM \"$posible\"")->fetchColumn();
            echo "Filas en $posible: $n\n";
        }
    }
} catch (Throwable $e) {
    echo 'ERROR de conexion/consulta: ' . $e->getMessage() . "\n";
}
