# Creating Custom Adapters

This guide explains how to create custom adapters for the Microsub plugin. Adapters allow reader plugins to integrate with the Microsub API.

## Overview

The Microsub plugin provides a flexible adapter system that separates the Microsub API from feed storage and management. Each adapter can handle specific types of feeds or provide complete feed management functionality.

## Basic Adapter Structure

Create a class that extends `\Microsub\Adapter`:

```php
<?php
namespace MyReader;

use Microsub\Adapter;

class Microsub_Adapter extends Adapter {
    /**
     * Unique identifier for this adapter.
     *
     * @var string
     */
    protected $id = 'my-reader';

    /**
     * Human-readable name for this adapter.
     *
     * @var string
     */
    protected $name = 'My Reader';
}
```

## Registering Your Adapter

Register your adapter during `plugins_loaded` with a priority after `10`:

```php
add_action( 'plugins_loaded', function() {
    if ( function_exists( '\\Microsub\\register_adapter' ) ) {
        $adapter = new \MyReader\Microsub_Adapter();
        $adapter->register();
    }
}, 20 );
```

## Required Methods

### get_channels

Returns the list of channels for a user.

```php
public function get_channels( $channels, $user_id ) {
    return array(
        array(
            'uid'    => 'notifications',
            'name'   => 'Notifications',
            'unread' => 5,
        ),
        array(
            'uid'    => 'default',
            'name'   => 'Home',
            'unread' => 'true', // or integer count
        ),
    );
}
```

### get_timeline

Returns timeline entries for a channel in jf2 format.

```php
public function get_timeline( $result, $channel, $args ) {
    // $args contains: before, after, limit
    return array(
        'items' => array(
            array(
                'type'      => 'entry',
                'published' => '2024-01-15T10:30:00+00:00',
                'url'       => 'https://example.com/post/1',
                'author'    => array(
                    'type'  => 'card',
                    'name'  => 'Jane Doe',
                    'url'   => 'https://example.com',
                    'photo' => 'https://example.com/photo.jpg',
                ),
                'content'   => array(
                    'text' => 'Hello, world!',
                    'html' => '<p>Hello, world!</p>',
                ),
            ),
        ),
        'paging' => array(
            'after'  => 'cursor-for-next-page',
            'before' => 'cursor-for-prev-page',
        ),
    );
}
```

### get_following

Returns the list of feeds followed in a channel.

```php
public function get_following( $result, $channel, $user_id ) {
    return array(
        'items' => array(
            array(
                'type' => 'feed',
                'url'  => 'https://example.com/feed',
                'name' => 'Example Blog',
            ),
        ),
    );
}
```

### follow

Subscribes to a feed URL in a channel.

```php
public function follow( $result, $channel, $url, $user_id ) {
    // Subscribe to the feed
    // Return the feed info on success
    return array(
        'type' => 'feed',
        'url'  => $url,
    );
}
```

### unfollow

Unsubscribes from a feed URL in a channel.

```php
public function unfollow( $result, $channel, $url, $user_id ) {
    // Unsubscribe from the feed
    return true;
}
```

## Optional Methods

Override these methods to support additional Microsub features:

### Channel Management

```php
public function create_channel( $result, $name, $user_id ) {
    // Create a new channel
    return array(
        'uid'  => 'new-channel-id',
        'name' => $name,
    );
}

public function update_channel( $result, $uid, $name, $user_id ) {
    // Update channel name
    return array(
        'uid'  => $uid,
        'name' => $name,
    );
}

public function delete_channel( $result, $uid, $user_id ) {
    // Delete a channel
    return true;
}

public function order_channels( $result, $uids, $user_id ) {
    // Reorder channels
    return true;
}
```

### Timeline Actions

```php
public function timeline_mark_read( $result, $channel, $entries, $user_id ) {
    // Mark entries as read
    // $entries can be an array of entry IDs or 'last_read_entry'
    return true;
}

public function timeline_mark_unread( $result, $channel, $entries, $user_id ) {
    // Mark entries as unread
    return true;
}

public function timeline_remove( $result, $channel, $entries, $user_id ) {
    // Remove entries from timeline
    return true;
}
```

### Mute/Block

```php
public function get_muted( $result, $channel, $user_id ) {
    return array( 'items' => array() );
}

public function mute( $result, $channel, $url, $user_id ) {
    return true;
}

public function unmute( $result, $channel, $url, $user_id ) {
    return true;
}

public function get_blocked( $result, $user_id ) {
    return array( 'items' => array() );
}

public function block( $result, $url, $user_id ) {
    return true;
}

public function unblock( $result, $url, $user_id ) {
    return true;
}
```

### Search and Preview

```php
public function search( $result, $query, $user_id ) {
    // Search for feeds
    return array(
        'results' => array(
            array(
                'type' => 'feed',
                'url'  => 'https://example.com/feed',
                'name' => 'Example Blog',
            ),
        ),
    );
}

public function preview( $result, $url, $user_id ) {
    // Preview a URL before following
    return array(
        'items' => array(
            // Sample entries from the feed
        ),
    );
}
```

## Multi-Adapter Support

When multiple adapters are registered, results are aggregated for read operations. For write operations (follow, unfollow), adapters can indicate whether they handle specific URLs.

### can_handle_url

Override this method to indicate if your adapter can subscribe to a specific URL:

```php
public function can_handle_url( $url ) {
    // Check if this adapter can handle the URL
    // For example, check if it's a valid RSS feed
    $response = wp_remote_get( $url );
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $content_type = wp_remote_retrieve_header( $response, 'content-type' );
    return strpos( $content_type, 'xml' ) !== false;
}
```

### owns_feed

Override this method to indicate if your adapter manages a specific feed:

```php
public function owns_feed( $url ) {
    // Check if this adapter manages the given feed URL
    // Used to determine which adapter handles unfollow requests
    $feed = $this->get_feed_by_url( $url );
    return ! empty( $feed );
}
```

## Complete Example

See the [Friends adapter](../includes/adapters/class-friends.php) for a complete implementation example.

```php
<?php
namespace MyReader;

use Microsub\Adapter;

class Microsub_Adapter extends Adapter {
    protected $id   = 'my-reader';
    protected $name = 'My Reader';

    public function get_channels( $channels, $user_id ) {
        $my_channels = $this->fetch_channels( $user_id );
        return array_merge( $channels, $my_channels );
    }

    public function get_timeline( $result, $channel, $args ) {
        $entries = $this->fetch_entries( $channel, $args );
        return array(
            'items'  => $entries,
            'paging' => $this->get_paging( $channel, $args ),
        );
    }

    public function get_following( $result, $channel, $user_id ) {
        $feeds = $this->fetch_feeds( $channel, $user_id );
        return array( 'items' => $feeds );
    }

    public function follow( $result, $channel, $url, $user_id ) {
        $feed = $this->subscribe( $channel, $url, $user_id );
        if ( is_wp_error( $feed ) ) {
            return $result; // Let another adapter handle it
        }
        return array(
            'type' => 'feed',
            'url'  => $url,
        );
    }

    public function unfollow( $result, $channel, $url, $user_id ) {
        return $this->unsubscribe( $channel, $url, $user_id );
    }

    public function can_handle_url( $url ) {
        return $this->can_discover_feed( $url );
    }

    public function owns_feed( $url ) {
        return $this->has_subscription( $url );
    }
}
```
