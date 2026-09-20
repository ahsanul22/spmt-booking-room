<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

abstract class PostgresTestCase extends TestCase
{
    private ?string $testSchema = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->testSchema = 'foundation_test_'.bin2hex(random_bytes(8));
        DB::statement('CREATE SCHEMA '.$this->testSchema);
        config(['database.connections.pgsql.search_path' => $this->testSchema]);
        DB::purge('pgsql');
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->seed();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testSchema !== null) {
                DB::statement('DROP SCHEMA '.$this->testSchema.' CASCADE');
            }
        } finally {
            parent::tearDown();
        }
    }
}
