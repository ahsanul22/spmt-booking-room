<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUpTraits()
    {
        $unsafeTraits = [
            \Illuminate\Foundation\Testing\RefreshDatabase::class,
            \Illuminate\Foundation\Testing\DatabaseMigrations::class,
            \Illuminate\Foundation\Testing\DatabaseTruncation::class,
        ];
        if (array_intersect($unsafeTraits, class_uses_recursive(static::class))) {
            throw new \LogicException('Database reset traits are forbidden. Use Tests\\PostgresTestCase with an isolated temporary schema.');
        }

        return parent::setUpTraits();
    }
}
