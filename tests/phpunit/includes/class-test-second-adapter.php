<?php
/**
 * Test Second Adapter.
 *
 * Second adapter for multi-adapter testing.
 *
 * @package Microsub
 */

namespace Microsub\Tests;

use Microsub\Adapter;

/**
 * Second test adapter for multi-adapter testing.
 */
class Test_Second_Adapter extends Adapter {

	/**
	 * Adapter identifier.
	 *
	 * @var string
	 */
	protected $id = 'second-adapter';

	/**
	 * Adapter name.
	 *
	 * @var string
	 */
	protected $name = 'Second Adapter';

	/**
	 * Get list of channels.
	 *
	 * @param array $channels Current channels.
	 * @param int   $user_id  User ID.
	 * @return array Channels.
	 */
	public function get_channels( $channels, $user_id ) {
		$channels[] = array(
			'uid'  => 'second-channel',
			'name' => 'Second Channel',
		);
		return $channels;
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
		return $result;
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
		return $result;
	}

	/**
	 * Follow a URL.
	 *
	 * @param array|null $result  Current result.
	 * @param string     $channel Channel UID.
	 * @param string     $url     URL to follow.
	 * @param int        $user_id User ID.
	 * @return array|null Result.
	 */
	public function follow( $result, $channel, $url, $user_id ) {
		return $result;
	}

	/**
	 * Unfollow a URL.
	 *
	 * @param bool|null $result  Current result.
	 * @param string    $channel Channel UID.
	 * @param string    $url     URL to unfollow.
	 * @param int       $user_id User ID.
	 * @return bool|null Result.
	 */
	public function unfollow( $result, $channel, $url, $user_id ) {
		return $result;
	}
}
