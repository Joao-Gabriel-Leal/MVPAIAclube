<?php

namespace Tests\Unit;

use App\Support\CardPublicUrl;
use Illuminate\Http\Request;
use Tests\TestCase;

class CardPublicUrlTest extends TestCase
{
    public function test_resolve_base_url_prefers_configured_public_base_url(): void
    {
        config([
            'app.card_public_base_url' => 'https://cards.clubehub.test/',
        ]);

        $request = Request::create('http://127.0.0.1:8000/perfil');

        $this->assertSame(
            'https://cards.clubehub.test',
            CardPublicUrl::resolveBaseUrl($request)
        );
    }

    public function test_resolve_base_url_keeps_the_current_host_when_it_is_not_loopback(): void
    {
        config([
            'app.card_public_base_url' => null,
        ]);

        $request = Request::create('http://192.168.0.55:8000/perfil');

        $this->assertSame(
            'http://192.168.0.55:8000',
            CardPublicUrl::resolveBaseUrl($request)
        );
    }

    public function test_resolve_base_url_replaces_loopback_with_the_detected_local_network_ip(): void
    {
        config([
            'app.card_public_base_url' => null,
        ]);

        $request = Request::create('http://127.0.0.1:8000/perfil');

        $this->assertSame(
            'http://192.168.0.55:8000',
            CardPublicUrl::resolveBaseUrl($request, [
                '127.0.0.1',
                '169.254.10.20',
                '192.168.0.55',
            ])
        );
    }

    public function test_build_validation_url_uses_the_detected_local_network_ip_for_loopback_requests(): void
    {
        config([
            'app.url' => 'http://127.0.0.1:8000',
            'app.card_public_base_url' => null,
        ]);

        $request = Request::create('http://127.0.0.1:8000/perfil');

        $this->assertSame(
            'http://192.168.0.55:8000/carteirinhas/token-local-001',
            CardPublicUrl::buildValidationUrl('token-local-001', $request, [
                '127.0.0.1',
                '192.168.0.55',
            ])
        );
    }
}
