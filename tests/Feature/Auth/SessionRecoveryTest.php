<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_cookie_name_stays_stable(): void
    {
        $this->assertSame('clube-aabb-session', config('session.cookie'));
    }
}
