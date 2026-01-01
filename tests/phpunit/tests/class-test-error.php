<?php
/**
 * Test Error class.
 *
 * @package Microsub
 */

namespace Microsub\Tests;

use WP_UnitTestCase;
use Microsub\Error;

/**
 * Test Error class.
 *
 * @coversDefaultClass \Microsub\Error
 */
class Test_Error extends WP_UnitTestCase {

	/**
	 * Test invalid_request returns 400 with message.
	 *
	 * @covers ::invalid_request
	 * @covers ::response
	 */
	public function test_invalid_request_error() {
		$response = Error::invalid_request( 'Test error message' );

		$this->assertInstanceOf( '\WP_REST_Response', $response );
		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'invalid_request', $data['error'] );
		$this->assertEquals( 'Test error message', $data['error_description'] );
	}

	/**
	 * Test unauthorized returns 401.
	 *
	 * @covers ::unauthorized
	 */
	public function test_unauthorized_error() {
		$response = Error::unauthorized();

		$this->assertEquals( 401, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'unauthorized', $data['error'] );
	}

	/**
	 * Test forbidden returns 403.
	 *
	 * @covers ::forbidden
	 */
	public function test_forbidden_error() {
		$response = Error::forbidden();

		$this->assertEquals( 403, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'forbidden', $data['error'] );
	}

	/**
	 * Test insufficient_scope returns 403 with message.
	 *
	 * @covers ::insufficient_scope
	 */
	public function test_insufficient_scope_error() {
		$response = Error::insufficient_scope( 'Requires read scope' );

		$this->assertEquals( 403, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'insufficient_scope', $data['error'] );
		$this->assertEquals( 'Requires read scope', $data['error_description'] );
	}

	/**
	 * Test not_found returns 404.
	 *
	 * @covers ::not_found
	 */
	public function test_not_found_error() {
		$response = Error::not_found();

		$this->assertEquals( 404, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'not_found', $data['error'] );
	}

	/**
	 * Test not_implemented returns 501.
	 *
	 * @covers ::not_implemented
	 */
	public function test_not_implemented_error() {
		$response = Error::not_implemented( 'Feature not available' );

		$this->assertEquals( 501, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'not_implemented', $data['error'] );
		$this->assertEquals( 'Feature not available', $data['error_description'] );
	}

	/**
	 * Test server_error returns 500.
	 *
	 * @covers ::server_error
	 */
	public function test_server_error() {
		$response = Error::server_error();

		$this->assertEquals( 500, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'server_error', $data['error'] );
	}

	/**
	 * Test generic response with custom error.
	 *
	 * @covers ::response
	 */
	public function test_generic_response() {
		$response = Error::response( 'custom_error', 'Custom description' );

		$this->assertEquals( 400, $response->get_status() );

		$data = $response->get_data();
		$this->assertEquals( 'custom_error', $data['error'] );
		$this->assertEquals( 'Custom description', $data['error_description'] );
	}

	/**
	 * Test response without description omits field.
	 *
	 * @covers ::response
	 */
	public function test_response_without_description() {
		$response = Error::response( 'invalid_request' );

		$data = $response->get_data();
		$this->assertEquals( 'invalid_request', $data['error'] );
		$this->assertArrayNotHasKey( 'error_description', $data );
	}
}
