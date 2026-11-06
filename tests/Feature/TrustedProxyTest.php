<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    public function test_proxy_https_header_makes_urls_secure(): void
    {
        // پشت Caddy (docker-compose.prod.yml) درخواست‌ها با هدر
        // X-Forwarded-Proto می‌رسند؛ لاراول باید آن را باور کند
        // و URLها و ریدایرکت‌ها https بمانند — نه http پشت پروکسی
        $this->get('/', ['X-Forwarded-Proto' => 'https']);

        $this->assertTrue(request()->isSecure());
        $this->assertStringStartsWith('https://', url('/'));
    }

    public function test_without_proxy_header_request_stays_http(): void
    {
        $this->get('/');

        $this->assertFalse(request()->isSecure());
        $this->assertStringStartsWith('http://', url('/'));
    }
}
