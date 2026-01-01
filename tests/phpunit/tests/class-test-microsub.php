<?php
/**
 * Test Microsub class.
 *
 * @package Microsub
 */

namespace Microsub\Tests;

use WP_UnitTestCase;
use Microsub\Microsub;

/**
 * Test Microsub class.
 */
class Test_Microsub extends WP_UnitTestCase {

	/**
	 * Test that the plugin is loaded.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'MICROSUB_VERSION' ) );
		$this->assertTrue( class_exists( '\Microsub\Microsub' ) );
	}

	/**
	 * Test that the endpoint URL is correct.
	 */
	public function test_endpoint_url() {
		$endpoint = Microsub::get_instance()->get_endpoint();
		$this->assertStringContainsString( 'microsub/1.0/endpoint', $endpoint );
	}

	/**
	 * Test that init adds hooks.
	 */
	public function test_init_hooks() {
		$microsub = Microsub::get_instance();
		$microsub->init();

		$this->assertNotFalse( has_action( 'wp_head', array( $microsub, 'html_header' ) ) );
		$this->assertNotFalse( has_action( 'send_headers', array( $microsub, 'http_header' ) ) );
	}
}
