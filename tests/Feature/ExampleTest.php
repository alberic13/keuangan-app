<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_bootstraps_with_the_expected_name(): void
    {
        $this->assertSame('E-Keuangan MAN 2 Surakarta', config('app.name'));
    }
}
