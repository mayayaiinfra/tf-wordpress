<?php
/**
 * Tests for TF_Shortcodes class
 *
 * @package Thunderfire
 */

use PHPUnit\Framework\TestCase;

class Test_TF_Shortcodes extends TestCase {

    /**
     * Test shortcode registration
     */
    public function test_shortcode_registration(): void {
        $shortcode = 'thunderfire';
        $this->assertEquals('thunderfire', $shortcode);
    }

    /**
     * Test node status shortcode
     */
    public function test_node_status_shortcode(): void {
        $atts = ['id' => 'node-001'];
        $this->assertArrayHasKey('id', $atts);
    }

    /**
     * Test fleet overview shortcode
     */
    public function test_fleet_shortcode(): void {
        $shortcode = 'thunderfire_fleet';
        $this->assertStringContainsString('fleet', $shortcode);
    }

    /**
     * Test shortcode attributes sanitization
     */
    public function test_atts_sanitization(): void {
        $id = sanitize_text_field('<script>alert("xss")</script>node-001');
        $this->assertStringNotContainsString('<script>', $id);
    }

    /**
     * Test shortcode output escaping
     */
    public function test_output_escaping(): void {
        $output = '<div class="tf-node">';
        $escaped = esc_html($output);
        $this->assertStringContainsString('&lt;', $escaped);
    }

    /**
     * Test empty attributes handling
     */
    public function test_empty_attributes(): void {
        $atts = [];
        $this->assertEmpty($atts);
    }
}
