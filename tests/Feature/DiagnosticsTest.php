<?php
declare(strict_types=1);

/** @noinspection PhpUndefinedClassInspection */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Lib\SessionUnitTest;
use Tests\TestCase;

class DiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test diagnostics.
     */
    public function testDiagnostics(): void
    {
        $response = $this->get('/Diagnostics');
        // code 200 something
        $response->assertStatus(200);

        $session_tests = new SessionUnitTest();
        $session_tests->log_as_id(0);

        $response = $this->get('/Diagnostics');
        // code 200 something
        $response->assertStatus(200);

        $response = $this->post('/api/Diagnostics');
        // code 200 something too
        $response->assertStatus(200);

        $response = $this->post('/api/Diagnostics::getSize');
        // code 200 something too
        $response->assertStatus(200);

        $session_tests->logout($this);
    }
}
