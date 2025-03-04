# Weave Team Member CPT Module

A simple, lightweight way to manage a team in WordPress. It adds a custom post type with some handy extra fields—no need for ACF. Comes with a block-based editor for entering team member details easily.

## Features

- Custom post type for Team Members
- Taxonomies for Locations and Job Roles
- Built-in meta fields for position, qualifications, phone, and email
- Gutenberg block for quick and easy data entry
- Custom admin columns for a better overview
- Works with the WordPress REST API
- Compatible with the Block Editor
- Plenty of filters for customisation
- Easily rename meta fields and taxonomies using filters

## Installation

You can install this in two ways: as a plugin or directly inside your theme.

### Option 1: Install as a Plugin (Recommended)

1. Upload the `weave-team-members-cpt` folder to `/wp-content/plugins/`
2. Activate the plugin via the WordPress admin under 'Plugins'
3. A new 'Team Members' menu will appear in the admin menu.
4. Adjust meta fields / taxonomies as needed.

### Option 2: Integrate into Your Client Theme

To add to a Weave starter theme build instead:

For detailed instructions on how to incorporate into a Weave starter theme (or any WordPress theme), please see [theme-integration-guide.md](theme-integration-guide.md).

## Usage

### Adding a Team Member / Staff Member

1. Go to 'Team Members' > 'Add New'
2. Enter their name in the title field
3. Use the Team Member Info block to add their details
4. Set a featured image (this becomes their profile picture)
5. Assign Location and Job Role if needed
6. Click 'Publish'

### Team Members Public Pages

By default, team member are only visible in the admin area and not publicly accessible on the front-end. This is ideal for sites that want to use team member data within templates, Beaver Themer or blocks, but don't want individual team member pages.

If you'd like to enable public team member pages and archives, you can use this filter in your theme's functions.php file:

```php
/**
 * Enable public pages for team members
 */
add_filter('weave_team_public', '__return_true');
```

This will enable:

- Individual team member pages
- Team archive page
- Inclusion in search results
- Ability to include team members in navigation menus

You can also modify specific arguments of the post type with:

```php
/**
 * Customize team post type settings
 */
add_filter('weave_team_post_type_args', function($args) {
	// Modify specific arguments
	$args['has_archive'] = true;
	$args['rewrite'] = array('slug' => 'our-team');
	
	return $args;
});
```

After adding either of these filters, remember to flush permalinks by visiting Settings > Permalinks in your WordPress admin.



### Displaying Team Members on Your Site

If not using a Page Builder use a custom query like this:

```php
$args = [
	'post_type' => 'weave_team',
	'posts_per_page' => -1,
	'orderby' => 'title',
	'order' => 'ASC'
];

$team_members = new WP_Query($args);

if ($team_members->have_posts()):
	while ($team_members->have_posts()): $team_members->the_post();
		$position = get_post_meta(get_the_ID(), '_weave_team_position', true);
		$qualification = get_post_meta(get_the_ID(), '_weave_team_qualification', true);
		$phone = get_post_meta(get_the_ID(), '_weave_team_phone', true);
		$email = get_post_meta(get_the_ID(), '_weave_team_email', true);
		
		// Your custom display code here
	endwhile;
	wp_reset_postdata();
endif;
```

### Using with Beaver Builder / Page Builder

This module works well with Beaver Builder. When setting up a Themer layout:

- Use "Post Title" for the team member’s name
- "Featured Image" for their photo
- "Post Content" for their biography
- "Post Custom Field" for position, qualifications, phone, and email

Field names for "Post Custom Field":
- `_weave_team_position`
- `_weave_team_qualification`
- `_weave_team_phone`
- `_weave_team_email`

Taxonomies for "Taxonomy Term":
- `weave_team_location`
- `weave_team_role`

## Customisation


For detailed instructions on how to customise fields, taxonomies, and other aspects of the Team Members Module, see the [customisation guide & examples](customisation-examples.md).


### Modifying Fields
```php
add_filter('weave_team_field_settings', function($field_settings) {
	$field_settings['phone']['enabled'] = false; // Disable phone field
	$field_settings['qualification']['label'] = 'Certifications:'; // Rename qualification field
	return $field_settings;
});
```

### Changing Taxonomies

```php
add_filter('weave_team_taxonomy_settings', function($taxonomy_settings) {
	$taxonomy_settings['location']['enabled'] = false; // Remove locations
	$taxonomy_settings['role']['label'] = 'Team Positions';
	return $taxonomy_settings;
});
```

### Adding a New Field

```php
add_filter('weave_team_field_settings', function($field_settings) {
	$field_settings['linkedin'] = [
		'enabled' => true,
		'label' => 'LinkedIn Profile:',
		'type' => 'url',
		'placeholder' => 'Enter LinkedIn URL...'
	];
	return $field_settings;
});
```

To register it for the REST API:

```php
add_filter('rest_api_init', function() {
	register_post_meta('weave_team', '_weave_team_linkedin', [
		'show_in_rest' => true,
		'single' => true,
		'type' => 'string',
		'auth_callback' => function() {
			return current_user_can('edit_posts');
		}
	]);
});
```

## Troubleshooting

- **Fields not showing?** Check if they are enabled in the settings.
- **Block missing?** Make sure JavaScript files are loading correctly.
- **Taxonomy terms not appearing?** Confirm the taxonomy is enabled.

For debugging, add this to the admin footer:

```php
add_action('admin_footer', function() {
	if (get_current_screen()->post_type !== 'weave_team') return;
	echo '<div style="display:none;"><pre>';
	print_r(weave_team_get_field_settings());
	print_r(weave_team_get_taxonomy_settings());
	echo '</pre></div>';
});
```

## Credits

Developed by [Weave Digital Studio](https://weave.co.nz)

