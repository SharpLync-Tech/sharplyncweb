<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoPagesTest extends TestCase
{
    public function test_home_has_local_metadata_and_self_referencing_canonical(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('IT Support Stanthorpe &amp; Granite Belt | SharpLync', false)
            ->assertSee('<link rel="canonical" href="https://sharplync.com.au/">', false)
            ->assertSee('Local IT Support for', false);
    }

    public function test_local_service_pages_are_indexable_and_self_canonical(): void
    {
        $pages = [
            '/it-support-stanthorpe' => 'IT Support Stanthorpe &amp; Granite Belt | SharpLync',
            '/computer-repairs-stanthorpe' => 'Computer Repairs Stanthorpe | Laptop &amp; Desktop Help | SharpLync',
        ];

        foreach ($pages as $path => $title) {
            $response = $this->get($path);

            $response->assertOk()
                ->assertSee($title, false)
                ->assertSee('<link rel="canonical" href="https://sharplync.com.au' . $path . '">', false)
                ->assertSee('<meta name="robots" content="index, follow">', false)
                ->assertDontSee('noindex', false)
                ->assertSee('FAQPage', false)
                ->assertSee('BreadcrumbList', false);
        }
    }

    public function test_sharpfleet_product_page_is_indexable_and_describes_the_saas_product(): void
    {
        $this->get('/products/sharpfleet')
            ->assertOk()
            ->assertSee('About SharpFleet | A SharpLync SaaS Product')
            ->assertSee('<link rel="canonical" href="https://sharplync.com.au/products/sharpfleet">', false)
            ->assertSee('SoftwareApplication', false)
            ->assertSee('Software as a Service', false)
            ->assertSee('Fleet Management', false)
            ->assertSee('Job Management', false)
            ->assertSee('href="https://sharpfleet.com.au"', false)
            ->assertSee('Open sharpfleet.com.au in a new tab', false);
    }

    public function test_duplicate_home_url_redirects_permanently(): void
    {
        $this->get('/home')->assertRedirect('/')->assertStatus(301);
    }

    public function test_legacy_service_and_policy_urls_redirect_permanently(): void
    {
        $this->get('/services/security')->assertRedirect('/services#cybersecurity')->assertStatus(301);
        $this->get('/services/cloud')->assertRedirect('/services#cloud-m365')->assertStatus(301);
        $this->get('/services/support')->assertRedirect('/it-support-stanthorpe')->assertStatus(301);
        $this->get('/policies/remote-support')->assertRedirect('/policies/support')->assertStatus(301);
    }

    public function test_removed_database_debug_routes_are_not_public(): void
    {
        foreach (['/test-services', '/test-menu', '/test-page', '/test-seo', '/email-preview'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_sitemap_contains_local_pages_and_excludes_auth_pages(): void
    {
        $xml = file_get_contents(public_path('sitemap.xml'));

        $this->assertIsString($xml);
        $this->assertStringContainsString('https://sharplync.com.au/it-support-stanthorpe', $xml);
        $this->assertStringContainsString('https://sharplync.com.au/computer-repairs-stanthorpe', $xml);
        $this->assertStringContainsString('https://sharplync.com.au/products/sharpfleet', $xml);
        $this->assertStringNotContainsString('https://sharplync.com.au/login', $xml);
        $this->assertStringNotContainsString('https://sharplync.com.au/register', $xml);
        $this->assertStringNotContainsString('policies/remote-support', $xml);
    }
}
