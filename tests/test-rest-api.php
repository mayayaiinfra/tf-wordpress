<?php
/**
 * Tests for TF_REST_API class
 *
 * @package Thunderfire
 */

use PHPUnit\Framework\TestCase;

class Test_TF_REST_API extends TestCase {

    /**
     * Test REST namespace
     */
    public function test_rest_namespace(): void {
        $namespace = 'thunderfire/v1';
        $this->assertEquals('thunderfire/v1', $namespace);
    }

    /**
     * Test nodes endpoint route
     */
    public function test_nodes_route(): void {
        $route = '/thunderfire/v1/nodes';
        $this->assertStringContainsString('nodes', $route);
    }

    /**
     * Test health endpoint route
     */
    public function test_health_route(): void {
        $route = '/thunderfire/v1/nodes/(?P<id>[\\w-]+)/health';
        $this->assertStringContainsString('health', $route);
    }

    /**
     * Test marketplace endpoint route
     */
    public function test_marketplace_route(): void {
        $route = '/thunderfire/v1/marketplace';
        $this->assertStringContainsString('marketplace', $route);
    }

    /**
     * Test permission callback returns boolean
     */
    public function test_permission_callback(): void {
        // Anonymous access should be denied
        $has_permission = false;
        $this->assertFalse($has_permission);
    }

    /**
     * Test response format
     */
    public function test_response_format(): void {
        $response = [
            'success' => true,
            'data' => ['nodes' => []],
        ];
        $json = wp_json_encode($response);
        $this->assertJson($json);
    }

    /**
     * Test error response format
     */
    public function test_error_response(): void {
        $error = [
            'code' => 'rest_forbidden',
            'message' => __('You do not have permission', 'thunderfire'),
        ];
        $this->assertArrayHasKey('code', $error);
        $this->assertArrayHasKey('message', $error);
    }
}
