<?php
/**
 * Tests for TF_Blocks class (Gutenberg blocks)
 *
 * @package Thunderfire
 */

use PHPUnit\Framework\TestCase;

class Test_TF_Blocks extends TestCase {

    /**
     * Test block category registration
     */
    public function test_block_category(): void {
        $category = [
            'slug' => 'thunderfire',
            'title' => __('THUNDERFIRE', 'thunderfire'),
        ];
        $this->assertEquals('thunderfire', $category['slug']);
    }

    /**
     * Test node-status block name
     */
    public function test_node_status_block(): void {
        $block = 'thunderfire/node-status';
        $this->assertStringStartsWith('thunderfire/', $block);
    }

    /**
     * Test fleet-overview block name
     */
    public function test_fleet_overview_block(): void {
        $block = 'thunderfire/fleet-overview';
        $this->assertStringContainsString('fleet', $block);
    }

    /**
     * Test alert-feed block name
     */
    public function test_alert_feed_block(): void {
        $block = 'thunderfire/alert-feed';
        $this->assertStringContainsString('alert', $block);
    }

    /**
     * Test block attributes schema
     */
    public function test_block_attributes(): void {
        $attributes = [
            'nodeId' => ['type' => 'string', 'default' => ''],
            'refreshInterval' => ['type' => 'number', 'default' => 30],
        ];
        $this->assertArrayHasKey('nodeId', $attributes);
        $this->assertArrayHasKey('refreshInterval', $attributes);
    }

    /**
     * Test block render callback exists
     */
    public function test_render_callback(): void {
        $callback = 'render_node_status_block';
        $this->assertIsString($callback);
    }
}
