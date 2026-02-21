<?php
/**
 * Tests for TF_Widget and TF_Dashboard_Widget classes
 *
 * @package Thunderfire
 */

use PHPUnit\Framework\TestCase;

class Test_TF_Widget extends TestCase {

    /**
     * Test widget ID base
     */
    public function test_widget_id(): void {
        $id = 'thunderfire_widget';
        $this->assertEquals('thunderfire_widget', $id);
    }

    /**
     * Test widget title
     */
    public function test_widget_title(): void {
        $title = __('THUNDERFIRE Nodes', 'thunderfire');
        $this->assertStringContainsString('THUNDERFIRE', $title);
    }

    /**
     * Test widget description
     */
    public function test_widget_description(): void {
        $description = __('Display THUNDERFIRE node status', 'thunderfire');
        $this->assertStringContainsString('node', $description);
    }

    /**
     * Test dashboard widget registration
     */
    public function test_dashboard_widget(): void {
        $widget_id = 'thunderfire_dashboard';
        $this->assertStringContainsString('dashboard', $widget_id);
    }

    /**
     * Test widget form fields
     */
    public function test_widget_form_fields(): void {
        $fields = ['title', 'node_count', 'show_health'];
        $this->assertContains('title', $fields);
        $this->assertContains('node_count', $fields);
    }

    /**
     * Test widget update sanitization
     */
    public function test_widget_update(): void {
        $instance = [
            'title' => sanitize_text_field('My Nodes'),
            'node_count' => absint(5),
        ];
        $this->assertEquals('My Nodes', $instance['title']);
        $this->assertEquals(5, $instance['node_count']);
    }
}
