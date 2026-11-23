<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetSupabaseRLSContext
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = Auth::user();

            if ($user instanceof Usuario && $this->isPostgreSQLConnection()) {
                $userId = (int) $user->id;
                $rolesJson = json_encode($user->getRoleNames()->values()->toArray(), JSON_THROW_ON_ERROR);
                $escapedRolesJson = str_replace("'", "''", $rolesJson);

                DB::unprepared("SET LOCAL app.current_user_id = '{$userId}'");
                DB::unprepared("SET LOCAL app.current_user_roles = '{$escapedRolesJson}'");

                if ($user->docente) {
                    $docenteId = (int) $user->docente->id;
                    DB::unprepared("SET LOCAL app.current_docente_id = '{$docenteId}'");

                    if ($user->docente->es_coordinador && $user->docente->modalidad_coordina_id) {
                        $modalidadId = (int) $user->docente->modalidad_coordina_id;
                        DB::unprepared("SET LOCAL app.current_coordinador_modalidad_id = '{$modalidadId}'");
                    }
                }
            }

            return $next($request);
        } catch (\Throwable $e) {
            Log::warning('No se pudo aplicar contexto RLS PostgreSQL', [
                'user_id' => Auth::id(),
                'driver' => $this->safeDriverName(),
                'exception' => $e->getMessage(),
            ]);

            return $next($request);
        }
    }

    protected function isPostgreSQLConnection(): bool
    {
        return $this->safeDriverName() === 'pgsql';
    }

    protected function safeDriverName(): string
    {
        try {
            return (string) DB::getDriverName();
        } catch (\Throwable) {
            return '';
        }
    }
}