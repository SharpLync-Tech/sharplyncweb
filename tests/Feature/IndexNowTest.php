<?php

namespace Tests\Feature;

use App\Services\IndexNowService;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class IndexNowTest extends TestCase
{
    public function test_key_file_contains_the_configured_key(): void
    {
        $key = config('seo.indexnow.key');

        $this->assertSame($key, trim((string) file_get_contents(public_path($key . '.txt'))));
    }

    public function test_command_submits_changed_urls(): void
    {
        Http::fake([
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        $this->artisan('seo:indexnow', ['urls' => ['/services', '/contact']])
            ->expectsOutputToContain('Submitted 2 URL(s)')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request->url() === 'https://api.indexnow.org/indexnow'
            && $request['host'] === 'sharplync.com.au'
            && $request['key'] === 'f7f209737ebe442c9308fc82aef471b2'
            && $request['keyLocation'] === 'https://sharplync.com.au/f7f209737ebe442c9308fc82aef471b2.txt'
            && $request['urlList'] === [
                'https://sharplync.com.au/services',
                'https://sharplync.com.au/contact',
            ]);
    }

    public function test_command_rejects_urls_from_another_host(): void
    {
        Http::preventStrayRequests();
        $this->expectException(InvalidArgumentException::class);

        app(IndexNowService::class)->submit(['https://example.com/page']);
    }
}
