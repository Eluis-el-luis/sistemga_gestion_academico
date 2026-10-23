<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * SALVAGUARDA DE SEGURIDAD: las pruebas automatizadas SOLO pueden correr
     * sobre SQLite en memoria. Si la conexión resuelta no es sqlite (por una
     * variable de entorno del shell del desarrollador), se aborta ANTES de
     * cualquier migrate/fresh, para no tocar nunca la base de datos real.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite') {
            $this->fail(
                'SEGURIDAD: las pruebas deben correr sobre SQLite, pero la conexión activa es "'
                . config('database.default') . '". No se ejecutará nada contra la base de datos real. '
                . 'Revisa phpunit.xml (los env usan force="true") y las variables DB_* de tu terminal.'
            );
        }

        if (config('database.connections.sqlite.database') !== ':memory:') {
            $this->fail(
                'SEGURIDAD: la suite regular debe usar SQLite en memoria (:memory:), pero apunta a "'
                . config('database.connections.sqlite.database') . '".'
            );
        }
    }
}
