<?php

namespace Tests\Unit\Helpers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for NavigationHelper functions.
 */
class NavigationHelperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test is_active_route function.
     */
    public function test_is_active_route(): void
    {
        // Mock the request to return a specific route
        $this->app['router']->get('/test-route', function () {
            return 'test';
        })->name('test.route');

        $this->get('/test-route');

        $this->assertTrue(is_active_route('test.route'));
        $this->assertFalse(is_active_route('other.route'));
    }

    /**
     * Test is_active_route_pattern function.
     */
    public function test_is_active_route_pattern(): void
    {
        // Mock the request to return a specific route
        $this->app['router']->get('/admin/dashboard', function () {
            return 'dashboard';
        })->name('admin.dashboard');

        $this->get('/admin/dashboard');

        $this->assertTrue(is_active_route_pattern('admin.*'));
        $this->assertTrue(is_active_route_pattern('admin.dashboard'));
        $this->assertFalse(is_active_route_pattern('user.*'));
    }

    /**
     * Test get_breadcrumbs function.
     */
    public function test_get_breadcrumbs(): void
    {
        // Mock the request to return a specific route
        $this->app['router']->get('/admin/products/create', function () {
            return 'create product';
        })->name('admin.products.create');

        $this->get('/admin/products/create');

        $breadcrumbs = get_breadcrumbs();

        $this->assertIsArray($breadcrumbs);
        $this->assertCount(3, $breadcrumbs);

        $this->assertEquals('Admin', $breadcrumbs[0]['name']);
        $this->assertEquals('Products', $breadcrumbs[1]['name']);
        $this->assertEquals('Create', $breadcrumbs[2]['name']);

        $this->assertTrue($breadcrumbs[2]['active']);
        $this->assertFalse($breadcrumbs[0]['active']);
    }

    /**
     * Test get_navigation_tree function.
     */
    public function test_get_navigation_tree(): void
    {
        $navigation = get_navigation_tree();

        $this->assertIsArray($navigation);
        $this->assertNotEmpty($navigation);

        // Check if main navigation items exist
        $navigationNames = array_column($navigation, 'name');
        $this->assertContains('Dashboard', $navigationNames);
        $this->assertContains('Products', $navigationNames);
        $this->assertContains('Licenses', $navigationNames);
        $this->assertContains('Customers', $navigationNames);
        $this->assertContains('Support', $navigationNames);
        $this->assertContains('Knowledge Base', $navigationNames);
        $this->assertContains('Settings', $navigationNames);

        // Check structure of navigation items
        foreach ($navigation as $item) {
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('route', $item);
            $this->assertArrayHasKey('icon', $item);
            $this->assertArrayHasKey('children', $item);
            $this->assertIsArray($item['children']);
        }
    }

    /**
     * Test get_available_languages function.
     */
    public function test_get_available_languages(): void
    {
        $languages = get_available_languages();

        $this->assertIsArray($languages);

        // Should contain at least the default languages
        $languageCodes = array_column($languages, 'code');
        $this->assertContains('en', $languageCodes);
        $this->assertContains('ar', $languageCodes);
        $this->assertContains('hi', $languageCodes);

        // Check structure of language items
        foreach ($languages as $language) {
            $this->assertArrayHasKey('code', $language);
            $this->assertArrayHasKey('name', $language);
            $this->assertArrayHasKey('flag', $language);
            $this->assertArrayHasKey('native_name', $language);
        }
    }

    /**
     * Test get_language_name function.
     */
    public function test_get_language_name(): void
    {
        $this->assertEquals('English', get_language_name('en'));
        $this->assertEquals('Arabic', get_language_name('ar'));
        $this->assertEquals('Hindi', get_language_name('hi'));
        $this->assertEquals('French', get_language_name('fr'));
        $this->assertEquals('Spanish', get_language_name('es'));

        // Test with unknown language code
        $this->assertEquals('Unknown', get_language_name('unknown'));
    }

    /**
     * Test get_language_flag function.
     */
    public function test_get_language_flag(): void
    {
        $this->assertEquals('🇺🇸', get_language_flag('en'));
        $this->assertEquals('🇸🇦', get_language_flag('ar'));
        $this->assertEquals('🇮🇳', get_language_flag('hi'));
        $this->assertEquals('🇫🇷', get_language_flag('fr'));
        $this->assertEquals('🇪🇸', get_language_flag('es'));

        // Test with unknown language code
        $this->assertEquals('🌐', get_language_flag('unknown'));
    }

    /**
     * Test get_language_native_name function.
     */
    public function test_get_language_native_name(): void
    {
        $this->assertEquals('English', get_language_native_name('en'));
        $this->assertEquals('العربية', get_language_native_name('ar'));
        $this->assertEquals('हिन्दी', get_language_native_name('hi'));
        $this->assertEquals('Français', get_language_native_name('fr'));
        $this->assertEquals('Español', get_language_native_name('es'));

        // Test with unknown language code
        $this->assertEquals('Unknown', get_language_native_name('unknown'));
    }

    /**
     * Test navigation tree structure for Products section.
     */
    public function test_navigation_tree_products_structure(): void
    {
        $navigation = get_navigation_tree();

        $productsItem = collect($navigation)->firstWhere('name', 'Products');

        $this->assertNotNull($productsItem);
        $this->assertEquals('admin.products.index', $productsItem['route']);
        $this->assertEquals('fas fa-box', $productsItem['icon']);
        $this->assertIsArray($productsItem['children']);
        $this->assertCount(3, $productsItem['children']);

        $childrenNames = array_column($productsItem['children'], 'name');
        $this->assertContains('All Products', $childrenNames);
        $this->assertContains('Product', $childrenNames);
        $this->assertContains('Categories', $childrenNames);
    }

    /**
     * Test navigation tree structure for Licenses section.
     */
    public function test_navigation_tree_licenses_structure(): void
    {
        $navigation = get_navigation_tree();

        $licensesItem = collect($navigation)->firstWhere('name', 'Licenses');

        $this->assertNotNull($licensesItem);
        $this->assertEquals('admin.licenses.index', $licensesItem['route']);
        $this->assertEquals('fas fa-key', $licensesItem['icon']);
        $this->assertIsArray($licensesItem['children']);
        $this->assertCount(2, $licensesItem['children']);

        $childrenNames = array_column($licensesItem['children'], 'name');
        $this->assertContains('All Licenses', $childrenNames);
        $this->assertContains('License Logs', $childrenNames);
    }

    /**
     * Test breadcrumbs with single level route.
     */
    public function test_breadcrumbs_single_level(): void
    {
        $this->app['router']->get('/dashboard', function () {
            return 'dashboard';
        })->name('dashboard');

        $this->get('/dashboard');

        $breadcrumbs = get_breadcrumbs();

        $this->assertIsArray($breadcrumbs);
        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Dashboard', $breadcrumbs[0]['name']);
        $this->assertTrue($breadcrumbs[0]['active']);
    }

    /**
     * Test breadcrumbs with no route.
     */
    public function test_breadcrumbs_no_route(): void
    {
        $this->get('/non-existent-route');

        $breadcrumbs = get_breadcrumbs();

        $this->assertIsArray($breadcrumbs);
        $this->assertEmpty($breadcrumbs);
    }
}
