<?php
/**
 * Friends Plugin Adapter for Microsub.
 *
 * @package Microsub
 */

namespace Microsub\Adapters;

use Microsub\Adapter;

/**
 * Friends Adapter
 *
 * Provides Microsub functionality using the Friends plugin as backend.
 *
 * @see https://github.com/akirk/friends
 */
class Friends extends Adapter {

	/**
	 * Adapter identifier.
	 *
	 * @var string
	 */
	protected $id = 'friends';

	/**
	 * Adapter name.
	 *
	 * @var string
	 */
	protected $name = 'Friends';

	/**
	 * Check if the Friends plugin is available.
	 *
	 * @return bool
	 */
	public static function is_available() {
		return \class_exists( '\Friends\Friends' );
	}

	/**
	 * Get the Friends plugin instance.
	 *
	 * @return \Friends\Friends|null
	 */
	protected function get_friends() {
		if ( ! self::is_available() ) {
			return null;
		}
		return \Friends\Friends::get_instance();
	}

	/**
	 * Check if this adapter can handle following the given URL.
	 *
	 * Friends plugin can handle most feed types (RSS, Atom, ActivityPub, mf2).
	 *
	 * @param string $url The URL to check.
	 * @return bool True if this adapter can handle the URL.
	 */
	public function can_handle_url( $url ) {
		if ( ! self::is_available() ) {
			return false;
		}

		$friends = $this->get_friends();
		if ( ! $friends || ! isset( $friends->feed ) ) {
			return false;
		}

		// Try to discover feeds from the URL.
		$discovered = $friends->feed->discover_feeds( $url );

		return ! \is_wp_error( $discovered ) && ! empty( $discovered );
	}

	/**
	 * Check if this adapter owns/manages the given feed URL.
	 *
	 * @param string $url The feed URL to check.
	 * @return bool True if this adapter owns the feed.
	 */
	public function owns_feed( $url ) {
		if ( ! self::is_available() ) {
			return false;
		}

		$feed = \Friends\User_Feed::get_by_url( $url );
		return ! empty( $feed );
	}

	/**
	 * Get list of channels.
	 *
	 * @param array $channels Current channels array from other adapters.
	 * @param int   $user_id  The user ID.
	 * @return array Array of channels with 'uid' and 'name'.
	 */
	public function get_channels( $channels, $user_id ) {
		if ( ! self::is_available() ) {
			return $channels;
		}

		// Add our channels to the existing list.
		$channels[] = array(
			'uid'    => 'notifications',
			'name'   => \__( 'Notifications', 'microsub' ),
			'unread' => 0,
		);

		$channels[] = array(
			'uid'  => 'home',
			'name' => \__( 'Home', 'microsub' ),
		);

		return $channels;
	}

	/**
	 * Get timeline entries for a channel.
	 *
	 * @param array  $result  Current result with 'items' from other adapters.
	 * @param string $channel Channel UID.
	 * @param array  $args    Query arguments (after, before, limit).
	 * @return array Timeline data with 'items' and optional 'paging'.
	 */
	public function get_timeline( $result, $channel, $args ) {
		if ( ! self::is_available() ) {
			return $result;
		}

		// Notifications channel returns empty for now.
		if ( 'notifications' === $channel ) {
			return $result;
		}

		// Only handle 'home' channel.
		if ( 'home' !== $channel ) {
			return $result;
		}

		$limit = isset( $args['limit'] ) ? \absint( $args['limit'] ) : 20;

		$query_args = array(
			'post_type'      => \Friends\Friends::CPT,
			'post_status'    => array( 'publish', 'private' ),
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		// Handle cursor-based pagination.
		if ( ! empty( $args['after'] ) ) {
			$query_args['date_query'] = array(
				array(
					'before' => $args['after'],
				),
			);
		}

		if ( ! empty( $args['before'] ) ) {
			$query_args['date_query'] = array(
				array(
					'after' => $args['before'],
				),
			);
			$query_args['order'] = 'ASC';
		}

		$query = new \WP_Query( $query_args );

		foreach ( $query->posts as $post ) {
			$result['items'][] = $this->post_to_jf2( $post );
		}

		return $result;
	}

	/**
	 * Get list of followed feeds in a channel.
	 *
	 * @param array  $result  Current result from other adapters.
	 * @param string $channel Channel UID.
	 * @param int    $user_id The user ID.
	 * @return array Array of feed objects with 'type' and 'url'.
	 */
	public function get_following( $result, $channel, $user_id ) {
		if ( ! self::is_available() ) {
			return $result;
		}

		// Get all friend users.
		$friend_users = \Friends\User_Query::all_friends_subscriptions();

		foreach ( $friend_users->get_results() as $friend_user ) {
			if ( ! $friend_user instanceof \Friends\User ) {
				continue;
			}

			$user_feeds = $friend_user->get_active_feeds();

			foreach ( $user_feeds as $user_feed ) {
				$feed = array(
					'type' => 'feed',
					'url'  => $user_feed->get_url(),
				);

				$name = $friend_user->display_name;
				if ( $name ) {
					$feed['name'] = $name;
				}

				$photo = \get_avatar_url( $friend_user->ID );
				if ( $photo ) {
					$feed['photo'] = $photo;
				}

				$result[] = $feed;
			}
		}

		return $result;
	}

	/**
	 * Follow a URL.
	 *
	 * @param array|null $result  Current result or null.
	 * @param string     $channel Channel UID.
	 * @param string     $url     URL to follow.
	 * @param int        $user_id The user ID.
	 * @return array|null Feed data on success, null to pass to next adapter.
	 */
	public function follow( $result, $channel, $url, $user_id ) {
		if ( ! self::is_available() ) {
			return $result;
		}

		// Check if we can handle this URL.
		if ( ! $this->can_handle_url( $url ) ) {
			return $result; // Pass to next adapter.
		}

		// Use Friends plugin to subscribe to the URL.
		$friend_user = \Friends\Subscription::subscribe( $url );

		if ( \is_wp_error( $friend_user ) ) {
			return $result;
		}

		return array(
			'type' => 'feed',
			'url'  => $url,
		);
	}

	/**
	 * Unfollow a URL.
	 *
	 * @param bool|null $result  Current result or null.
	 * @param string    $channel Channel UID.
	 * @param string    $url     URL to unfollow.
	 * @param int       $user_id The user ID.
	 * @return bool|null True on success, false on failure, null to pass to next adapter.
	 */
	public function unfollow( $result, $channel, $url, $user_id ) {
		if ( ! self::is_available() ) {
			return $result;
		}

		// Check if we own this feed.
		if ( ! $this->owns_feed( $url ) ) {
			return $result; // Pass to next adapter.
		}

		// Find the feed by URL.
		$feed = \Friends\User_Feed::get_by_url( $url );

		if ( ! $feed ) {
			return false;
		}

		$friend_user = $feed->get_friend_user();

		if ( ! $friend_user ) {
			return false;
		}

		// Check if user has other feeds.
		$user_feeds = $friend_user->get_active_feeds();

		if ( \count( $user_feeds ) <= 1 ) {
			// Delete the user if this is the only feed.
			\wp_delete_user( $friend_user->ID );
		} else {
			// Just deactivate this feed.
			$feed->update_metadata( 'active', false );
		}

		return true;
	}

	/**
	 * Search for feeds.
	 *
	 * @param array|null $result  Current result or null.
	 * @param string     $query   Search query.
	 * @param int        $user_id The user ID.
	 * @return array|null Search results.
	 */
	public function search( $result, $query, $user_id ) {
		if ( ! self::is_available() ) {
			return $result;
		}

		$friends = $this->get_friends();
		if ( ! $friends || ! isset( $friends->feed ) ) {
			return $result;
		}

		// Use Friends feed discovery.
		$discovered = $friends->feed->discover_feeds( $query );

		if ( \is_wp_error( $discovered ) || empty( $discovered ) ) {
			return array( 'results' => array() );
		}

		$results = array();

		foreach ( $discovered as $feed_url => $feed_data ) {
			$item = array(
				'type' => 'feed',
				'url'  => $feed_url,
			);

			if ( ! empty( $feed_data['title'] ) ) {
				$item['name'] = $feed_data['title'];
			}

			$results[] = $item;
		}

		return array( 'results' => $results );
	}

	/**
	 * Preview a URL.
	 *
	 * @param array|null $result  Current result or null.
	 * @param string     $url     URL to preview.
	 * @param int        $user_id The user ID.
	 * @return array|null Preview data.
	 */
	public function preview( $result, $url, $user_id ) {
		if ( ! self::is_available() ) {
			return $result;
		}

		$friends = $this->get_friends();
		if ( ! $friends || ! isset( $friends->feed ) ) {
			return $result;
		}

		// Try to fetch and parse the feed.
		$discovered = $friends->feed->discover_feeds( $url );

		if ( \is_wp_error( $discovered ) || empty( $discovered ) ) {
			return $result;
		}

		// Get first feed.
		$feed_url  = \array_key_first( $discovered );
		$feed_data = $discovered[ $feed_url ];

		$items = array();

		if ( ! empty( $feed_data['entries'] ) ) {
			foreach ( \array_slice( $feed_data['entries'], 0, 10 ) as $entry ) {
				$items[] = $this->to_jf2( $entry );
			}
		}

		return array( 'items' => $items );
	}

	/**
	 * Convert a WordPress post to jf2 format.
	 *
	 * @param \WP_Post $post The post object.
	 * @return array jf2 formatted entry.
	 */
	protected function post_to_jf2( $post ) {
		$jf2 = array(
			'type'      => 'entry',
			'_id'       => (string) $post->ID,
			'published' => \get_the_date( 'c', $post ),
		);

		// URL.
		$permalink = \get_post_meta( $post->ID, 'permalink', true );
		if ( $permalink ) {
			$jf2['url'] = $permalink;
		}

		// Title.
		$title = \get_the_title( $post );
		if ( $title && $title !== $permalink ) {
			$jf2['name'] = $title;
		}

		// Content.
		$content = \get_the_content( null, false, $post );
		if ( $content ) {
			$jf2['content'] = array(
				'html' => $content,
				'text' => \wp_strip_all_tags( $content ),
			);
		}

		// Author from post author (Friends user).
		$author = \get_userdata( $post->post_author );
		if ( $author ) {
			$jf2['author'] = array(
				'type'  => 'card',
				'name'  => $author->display_name,
				'url'   => $author->user_url,
				'photo' => \get_avatar_url( $author->ID ),
			);
		}

		// Post format.
		$post_format = \get_post_format( $post );
		if ( $post_format ) {
			$jf2['post-type'] = $post_format;
		}

		return $jf2;
	}
}
