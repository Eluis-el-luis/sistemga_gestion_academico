<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetSupabaseRLSContext
{
    /**
     * Handle an incoming request.
     *
     * Configura variables de sesión PostgreSQL para que las políticas RLS
     * en Supabase tengan el contexto del usuario autenticado de Laravel.
     *
     * Estas variables son leídas por las funciones SQL:
     * - current_user_id()
     * - current_user_roles()
     * - current_docente_id()
     * - current_coordinador_modalidad_id()
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo ejecutar si hay usuario autenticado y conexión es PostgreSQL/Supabase
        if (Auth::check() && $this->isPostgreSQLConnection()) {
            $user = Auth::user();
            $userId = $user->id;
            
            // Obtener roles del usuario como array de strings
            $roles = $user->getRoleNames()->toArray();
            $rolesJson = json_encode($roles);
            
            // Ejecutar SET LOCAL para la transacción actual
            // Usar DB::unprepared para evitar prepared statements con variables de sesión
            // SET LOCAL solo afecta la transacción actual (ideal para request/response)
            DB::unprepared("SET LOCAL app.current_user_id = '{$userId}'");
            DB::unprepared("SET LOCAL app.current_user_roles = '{$rolesJson}'");
            
            // También setear docente_id si existe
            if ($user->docente) {
                DB::unprepared("SET LOCAL app.current_docente_id = '{$user->docente->id}'");
                
                // Si es coordinador, setear modalidad que coordina
                if ($user->docente->es_coordinador && $user->docente->modalidad_coordina_id) {
                    DB::unprepared("SET LOCAL app.current_coordinador_modalidad_id = '{$user->docente->modalidad_coordina_id}'");
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * Verificar si la conexión actual es PostgreSQL (Supabase)
     */
    protected function isPostgreSQLConnection(): bool
    {
        try {
            $driver = DB::getDriverName();
            return $driver === 'pgsql';
        } catch (\Throwable) {
            return false;
        }
    }
}