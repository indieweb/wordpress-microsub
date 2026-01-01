<?php
/**
 * Test Adapter class.
 *
 * @package Microsub
 */

namespace Microsub\Tests;

use WP_UnitTestCase;
use Microsub\Adapter;

/**
 * Concrete adapter implementation for testing.
 */
class Test_Adapter_Implementation extends Adapter {

	protected $id = 'test-adapter';
	protected $name = 'Test Adapter';

	public function get_channels( $channels, $user_id ) {
		return array(
			array(
				'uid'  => 'notifications',
				'name' => 'Notifications',
			),
			array(
				'uid'  => 'default',
				'name' => 'Home',
			),
		);
	}

	public function get_timeline( $result, $channel, $args ) {
		return array(
			'items' => array(
				array(
					'type' => 'entry',
					'_id'  => 'test-1',
					'url'  => 'https://example.com/post/1',
				),
			),
		);
	}

	public function get_following( $result, $channel, $user_id ) {
		return array(
			array(
				'type' => 'feed',
				'url'  => 'https://example.com/feed',
			),
		);
	}

	public function follow( $result, $channel, $url, $user_id ) {
		return array(
			'type' => 'feed',
			'url'  => $url,
		);
	}

	public function unfollow( $result, $channel, $url, $user_id ) {
		return true;
	}
}

/**
 * Test Adapter class.
 */
class Test_Adapter extends WP_UnitTestCase {

	/**
	 * Test adapter instance.
	 *
	 * @var Test_Adapter_Implementation
	 */
	private $adapter;

	/**
	 * Set up test fixtures.
	 */
	public function set_up() {
		parent::set_up();
		$this->adapter = new Test_Adapter_Implementation();
	}

	/**
	 * Test that adapter can be registered.
	 */
	public function test_register_adapter() {
		$this->adapter->register();

		$adapters = apply_filters( 'microsub_adapters', array() );

		$this->assertArrayHasKey( 'test-adapter', $adapters );
		$this->assertEquals( 'Test Adapter', $adapters['test-adapter']['name'] );
	}

	/**
	 * Test get_channels filter.
	 */
	public function test_get_channels_filter() {
		$this->adapter->register();

		$channels = apply_filters( 'microsub_get_channels', null, 1 );

		$this->assertIsArray( $channels );
		$this->assertCount( 2, $channels );
		$this->assertEquals( 'notifications', $channels[0]['uid'] );
	}

	/**
	 * Test get_timeline filter.
	 */
	public function test_get_timeline_filter() {
		$this->adapter->register();

		$result = apply_filters( 'microsub_get_timeline', null, 'default', array() );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'items', $result );
		$this->assertCount( 1, $result['items'] );
	}

	/**
	 * Test follow filter.
	 */
	public function test_follow_filter() {
		$this->adapter->register();

		$result = apply_filters( 'microsub_follow', null, 'default', 'https://example.com', 1 );

		$this->assertIsArray( $result );
		$this->assertEquals( 'feed', $result['type'] );
		$this->assertEquals( 'https://example.com', $result['url'] );
	}

	/**
	 * Test adapter ID and name getters.
	 */
	public function test_adapter_getters() {
		$this->assertEquals( 'test-adapter', $this->adapter->get_id() );
		$this->assertEquals( 'Test Adapter', $this->adapter->get_name() );
	}

	/**
	 * Test get_following filter.
	 */
	public function test_get_following_filter() {
		$this->adapter->register();

		$result = apply_filters( 'microsub_get_following', array(), 'default', 1 );

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result );
		$this->assertEquals( 'feed', $result[0]['type'] );
	}

	/**
	 * Test unfollow filter.
	 */
	public function test_unfollow_filter() {
		$this->adapter->register();

		$result = apply_filters( 'microsub_unfollow', null, 'default', 'https://example.com', 1 );

		$this->assertTrue( $result );
	}

	/**
	 * Test multiple adapters are aggregated.
	 */
	public function test_multiple_adapters() {
		$this->adapter->register();

		$second_adapter = new Test_Second_Adapter();
		$second_adapter->register();

		$adapters = apply_filters( 'microsub_adapters', array() );

		$this->assertCount( 2, $adapters );
		$this->assertArrayHasKey( 'test-adapter', $adapters );
		$this->assertArrayHasKey( 'second-adapter', $adapters );
	}

	/**
	 * Test channels are aggregated from multiple adapters.
	 */
	public function test_channels_aggregation() {
		$this->adapter->register();

		$second_adapter = new Test_Second_Adapter();
		$second_adapter->register();

		$channels = apply_filters( 'microsub_get_channels', array(), 1 );

		$this->assertCount( 3, $channels );
	}

	/**
	 * Test can_handle_url default implementation.
	 */
	public function test_can_handle_url_default() {
		$this->assertFalse( $this->adapter->can_handle_url( 'https://example.com' ) );
	}

	/**
	 * Test owns_feed default implementation.
	 */
	public function test_owns_feed_default() {
		$this->assertFalse( $this->adapter->owns_feed( 'https://example.com/feed' ) );
	}
}

class Test_Second_Adapter extends \Microsub\Adapter {

	protected $id = 'second-adapter';
	protected $name = 'Second Adapter';

	public function get_channels( $channels, $user_id ) {
		$channels[] = array(
			'uid'  => 'second-channel',
			'name' => 'Second Channel',
		);
		return $channels;
	}

	public function get_timeline( $result, $channel, $args ) {
		return $result;
	}

	public function get_following( $result, $channel, $user_id ) {
		return $result;
	}

	public function follow( $result, $channel, $url, $user_id ) {
		return $result;
	}

	public function unfollow( $result, $channel, $url, $user_id ) {
		return $result;
	}
}
