@echo off
REM =====================================================================
REM  Servidor dedicado para las pruebas E2E (Laravel Dusk).
REM
REM  1) FUERZA el entorno SQLite de pruebas sin importar las variables DB_*
REM    que puedan existir en la sesion del desarrollador: si el shell tiene
REM    DB_CONNECTION=pgsql, este launcher lo neutraliza antes de arrancar
REM    `php artisan serve`, garantizando que el navegador de los tests
REM    NUNCA hable con la base de datos real (Supabase).
REM
REM  2) Usa el PHP de Laragon (8.3 con extensiones), no el del PATH.
REM
REM  Uso:   scripts\servidor-e2e.cmd
REM  Detener: Ctrl+C en esta ventana (o cerrarla).
REM =====================================================================

REM --- Localizar el PHP de Laragon (toma el ultimo php-* encontrado) ---
set "PHPEXE="
for /d %%D in ("E:\laragon\bin\php\php-*") do set "PHPEXE=%%D\php.exe"
if "%PHPEXE%"=="" (
    echo [servidor-e2e] No se encontro PHP en E:\laragon\bin\php; usando el del PATH.
    set "PHPEXE=php"
)

REM --- Forzar entorno de pruebas E2E (SQLite dedicado) ---
set "DB_CONNECTION=sqlite"
set "DB_DATABASE=%~dp0..\database\dusk.sqlite"
set "DB_URL="
set "DB_HOST="
set "DB_PORT="
set "DB_USERNAME="
set "DB_PASSWORD="
set "SESSION_DRIVER=file"
set "MAIL_MAILER=array"
set "QUEUE_CONNECTION=sync"
set "E2E_SEED=1"

REM CD al raiz del proyecto (carpeta padre de scripts/)
cd /d "%~dp0.."

echo [servidor-e2e] PHP: %PHPEXE%
echo [servidor-e2e] BD : %DB_DATABASE%
"%PHPEXE%" artisan serve --env=dusk --host=127.0.0.1 --port=8299 --no-reload
