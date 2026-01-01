<?php
/**
 * Test Adapter Implementation.
 *
 * Concrete adapter implementation for testing.
 *
 * @package Microsub
 */

namespace Microsub\Tests;

use Microsub\Adapter;

/**
 * Test adapter implementation for testing.
 */
class Test_Adapter_Implementation extends Adapter {

	/**
	 * Adapter identifier.
	 *
	 * @var string
	 */
	protected $id = 'test-adapter';

	/**
	 * Adapter name.
	 *
	 * @var string
	 */
	protected $name = 'Test Adapter';

	/**
	 * Get list of channels.
	 *
	 * @param array $channels Current channels.
	 * @param int   $user_id  User ID.
	 * @return array Channels.
	 */
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

	/**
	 * Get timeline entries.
	 *
	 * @param array  $result  Current result.
	 * @param string $channel Channel UID.
	 * @param array  $args    Query args.
	 * @return array Timeline data.
	 */
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

	/**
	 * Get followed feeds.
	 *
	 * @param array  $result  Current result.
	 * @param string $channel Channel UID.
	 * @param int    $user_id User ID.
	 * @return array Feeds.
	 */
	public function get_following( $result, $channel, $user_id ) {
		return array(
			array(
				'type' => 'feed',
				'url'  => 'https://example.com/feed',
			),
		);
	}

	/**
	 * Follow a URL.
	 *
	 * @param array|null $result  Current result.
	 * @param string     $channel Channel UID.
	 * @param string     $url     URL to follow.
	 * @param int        $user_id User ID.
	 * @return array Feed data.
	 */
	public function follow( $result, $channel, $url, $user_id ) {
		return array(
			'type' => 'feed',
			'url'  => $url,
		);
	}

	/**
	 * Unfollow a URL.
	 *
	 * @param bool|null $result  Current result.
	 * @param string    $channel Channel UID.
	 * @param string    $url     URL to unfollow.
	 * @param int       $user_id User ID.
	 * @return bool True on success.
	 */
	public function unfollow( $result, $channel, $url, $user_id ) {
		return true;
	}
}
