<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class DatabaseTestSafetyTest extends TestCase
{
    public function test_reset_trait_is_rejected_before_any_application_or_database_access(): void
    {
        $unsafe = new class('testUnused') extends \Tests\TestCase {
            use RefreshDatabase;

            public function checkTraits(): void
            {
                $this->setUpTraits();
            }

            public function testUnused(): void {}
        };

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Database reset traits are forbidden');
        $unsafe->checkTraits();
    }
}
