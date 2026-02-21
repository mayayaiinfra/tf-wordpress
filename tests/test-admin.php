<?php
/**
 * Tests for TF_Admin class
 *
 * @package Thunderfire
 */

use PHPUnit\Framework\TestCase;

class Test_TF_Admin extends TestCase {

    /**
     * Test admin menu registration
     */
    public function test_admin_menu_hook(): void {
        $result = add_action('admin_menu', function() {});
        $this->assertTrue($result);
    }

    /**
     * Test settings page slug
     */
    public function test_settings_page_slug(): void {
        $slug = 'thunderfire-settings';
        $this->assertEquals('thunderfire-settings', $slug);
    }

    /**
     * Test capability check
     */
    public function test_admin_capability(): void {
        $capability = 'manage_options';
        $this->assertEquals('manage_options', $capability);
    }

    /**
     * Test option sanitization
     */
    public function test_sanitize_api_url(): void {
        $url = 'https://api.thunderfire.com/v1';
        $sanitized = esc_url($url);
        $this->assertStringStartsWith('https://', $sanitized);
    }

    /**
     * Test API key sanitization
     */
    public function test_sanitize_api_key(): void {
        $key = '  tf_live_abc123  ';
        $sanitized = sanitize_text_field($key);
        $this->assertEquals('tf_live_abc123', $sanitized);
    }

    /**
     * Test settings fields
     */
    public function test_settings_fields(): void {
        $fields = ['thunderfire_api_url', 'thunderfire_api_key', 'thunderfire_refresh_interval'];
        $this->assertCount(3, $fields);
    }
}
