<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class DatabaseIsolationTest extends TestCase
{
    public function test_testing_uses_isolated_sqlite_database(): void
    {
        $this->assertSame(
            'sqlite',
            config('database.default')
        );

        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );
    }
}
