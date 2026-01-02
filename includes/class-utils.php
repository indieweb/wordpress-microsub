<?php
/**
 * Utility functions for Microsub.
 *
 * @package Microsub
 */

namespace Microsub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Utils
 *
 * Shared utility functions for Microsub operations.
 */
class Utils {

	/**
	 * Sort items by published date (newest first).
	 *
	 * @param array $items Items to sort.
	 * @return array Sorted items.
	 */
	public static function sort_items_by_date( $items ) {
		\usort(
			$items,
			function ( $a, $b ) {
				$date_a = isset( $a['published'] ) ? \strtotime( $a['published'] ) : 0;
				$date_b = isset( $b['published'] ) ? \strtotime( $b['published'] ) : 0;
				return $date_b - $date_a;
			}
		);

		return $items;
	}

	/**
	 * Deduplicate items by their _id key.
	 *
	 * @param array $items Items to filter.
	 * @return array Deduplicated items.
	 */
	public static function dedupe_items_by_id( $items ) {
		$unique = array();
		$seen   = array();

		foreach ( $items as $item ) {
			$id = isset( $item['_id'] ) ? $item['_id'] : null;

			if ( $id && isset( $seen[ $id ] ) ) {
				continue;
			}

			if ( $id ) {
				$seen[ $id ] = true;
			}

			$unique[] = $item;
		}

		return $unique;
	}

	/**
	 * Merge, deduplicate, and sort items by date.
	 *
	 * Convenience method that combines common operations.
	 *
	 * @param array $items Items to process.
	 * @return array Processed items.
	 */
	public static function merge_and_sort_items( $items ) {
		$items = self::dedupe_items_by_id( $items );
		$items = self::sort_items_by_date( $items );
		return $items;
	}

	/**
	 * Convert a WordPress post to jf2 format.
	 *
	 * @param \WP_Post $post The post object.
	 * @return array jf2 formatted entry.
	 */
	public static function post_to_jf2( $post ) {
		$jf2 = array(
			'type'      => 'entry',
			'_id'       => 'post-' . $post->ID,
			'published' => \get_the_date( 'c', $post ),
			'url'       => \get_permalink( $post ),
		);

		// Title.
		$title = \get_the_title( $post );
		if ( $title ) {
			$jf2['name'] = $title;
		}

		// Content.
		$content = \apply_filters( 'the_content', $post->post_content );
		if ( $content ) {
			$jf2['content'] = array(
				'html' => $content,
				'text' => \wp_strip_all_tags( $content ),
			);
		}

		// Summary/Excerpt.
		$excerpt = \get_the_excerpt( $post );
		if ( $excerpt && $excerpt !== $title ) {
			$jf2['summary'] = $excerpt;
		}

		// Author.
		$author = \get_userdata( $post->post_author );
		if ( $author ) {
			$jf2['author'] = array(
				'type'  => 'card',
				'name'  => $author->display_name,
				'url'   => \get_author_posts_url( $author->ID ),
				'photo' => \get_avatar_url( $author->ID ),
			);
		}

		// Featured image.
		if ( \has_post_thumbnail( $post ) ) {
			$jf2['photo'] = \get_the_post_thumbnail_url( $post, 'large' );
		}

		// Categories as tags.
		$categories = \get_the_category( $post->ID );
		if ( ! empty( $categories ) ) {
			$jf2['category'] = \array_map(
				function ( $cat ) {
					return $cat->name;
				},
				$categories
			);
		}

		/**
		 * Filters the jf2 formatted entry for a post.
		 *
		 * @param array    $jf2  The jf2 entry.
		 * @param \WP_Post $post The original post.
		 */
		return \apply_filters( 'microsub_post_to_jf2', $jf2, $post );
	}
}
