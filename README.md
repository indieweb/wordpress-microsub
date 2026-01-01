# Microsub

A Microsub server reference implementation for WordPress.

[Microsub](https://indieweb.org/Microsub-spec) is a standardized API for creating and managing feeds. It separates feed reading clients from feed aggregation servers, allowing users to use any compatible client with any compatible server.

This plugin implements the server side of the Microsub specification. It provides a flexible adapter system that allows reader plugins to integrate with the Microsub API.

## Documentation

- [Creating Custom Adapters](docs/adapters.md) - Guide for integrating reader plugins with Microsub.

## Requirements

- WordPress 6.5 or higher
- PHP 7.4 or higher
- [IndieAuth](https://wordpress.org/plugins/indieauth/) plugin for authentication

## Installation

1. Upload the `microsub` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Install and activate the IndieAuth plugin
4. Install a reader plugin that provides a Microsub adapter

## API Endpoint

The Microsub endpoint is available at:

```
/wp-json/microsub/1.0/endpoint
```

Discovery is automatically added to your site's HTML `<head>` and HTTP headers:

```html
<link rel="microsub" href="https://example.com/wp-json/microsub/1.0/endpoint" />
```

## Authentication

This plugin requires the [IndieAuth plugin](https://wordpress.org/plugins/indieauth/) for authentication. Clients authenticate using OAuth 2.0 Bearer tokens obtained through the IndieAuth flow.

### Scopes

- `read` - Required for timeline, search, and preview actions
- `channels` - Required for channel management
- `follow` - Required for follow/unfollow actions
- `mute` - Required for mute/unmute actions
- `block` - Required for block/unblock actions

## Available Filters

### Channel Operations

- `microsub_get_channels` - Get list of channels
- `microsub_create_channel` - Create a new channel
- `microsub_update_channel` - Update a channel
- `microsub_delete_channel` - Delete a channel
- `microsub_order_channels` - Reorder channels

### Timeline Operations

- `microsub_get_timeline` - Get timeline entries
- `microsub_timeline_mark_read` - Mark entries as read
- `microsub_timeline_mark_unread` - Mark entries as unread
- `microsub_timeline_remove` - Remove entries

### Follow Operations

- `microsub_get_following` - Get followed feeds
- `microsub_follow` - Follow a URL
- `microsub_unfollow` - Unfollow a URL

### Mute/Block Operations

- `microsub_get_muted` - Get muted users
- `microsub_mute` - Mute a user
- `microsub_unmute` - Unmute a user
- `microsub_get_blocked` - Get blocked users
- `microsub_block` - Block a user
- `microsub_unblock` - Unblock a user

### Search and Preview

- `microsub_search` - Search for feeds
- `microsub_preview` - Preview a URL

## Development

### Local Development with wp-env

This plugin includes a [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) configuration for local development.

```bash
# Install dependencies
npm install

# Start the environment
npm start

# Stop the environment
npm stop

# Run PHPUnit tests in wp-env
npm run test:php
```

The local environment will be available at http://localhost:8686 (admin: admin/password).

### Running Tests

```bash
composer install
composer test
```

### Linting

```bash
composer lint
composer lint:fix
```

## Support

If you need help, [check out the support forums on WordPress.org](https://wordpress.org/support/plugin/microsub/).

## Contribute

Contributions are welcome! Please feel free to submit a Pull Request.

## License

[GPL-2.0-or-later](LICENSE)

## Credits

Developed by [Matthias Pfefferle](https://notiz.blog)

Based on the [Microsub specification](https://indieweb.org/Microsub-spec) from the IndieWeb community.
