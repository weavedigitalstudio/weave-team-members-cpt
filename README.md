# Weave Team Members Plugin

A simple, lightweight WordPress plugin to manage team members. It adds a custom post type with built-in meta fields and taxonomies—no need for ACF. Comes with a block-based editor for entering team member details easily. Built for internal use by Weave Digital Studio and HumanKind Funeral Websites but shared for anyone to use.

## Features

- **Custom Post Type** for Team Members with admin-friendly interface
- **Built-in Taxonomies** for Locations and Job Roles (fully customisable)
- **Meta Fields** for position, qualifications, phone, and email (can be disabled/renamed)
- **Gutenberg Block** for quick and easy data entry in the block editor
- **Custom Admin Columns** with featured images and sortable fields
- **Post Ordering Support** - compatible with Simple Page Ordering and similar plugins
- **REST API Compatible** - works with headless WordPress setups
- **Highly Customisable** - extensive filter system for fields and taxonomies
- **Private by Default** - no public pages unless you want them
- **Beaver Builder Compatible** - works seamlessly with Beaver Themer

## Installation

**Install as a Plugin:**

1. Upload the `weave-team-members-cpt` folder to `/wp-content/plugins/`
2. Activate the plugin via the WordPress admin under 'Plugins'
3. A new 'Team Members' menu will appear in the admin menu
4. Start adding team members!

**Automatic Updates:**

This plugin includes automatic update functionality that checks for new releases on GitHub. When a new version is available, you'll see an update notification in your WordPress admin just like any other plugin. The plugin will automatically download and install updates from the GitHub repository.

## Basic Usage

### Adding a Team Member

1. Go to **Team Members** > **Add New**
2. Enter their name in the title field
3. Add their biography in the main content area
4. Use the **Team Member Info** block to add their details:
   - Position/Job Title
   - Qualifications/Certifications
   - Phone number
   - Email address
5. Set a **Featured Image** (this becomes their profile picture)
6. Assign **Location** and **Job Role** taxonomies if needed
7. Click **Publish**

### Team Members Public Pages

By default, team members are only visible in the admin area and not publicly accessible on the front-end. This is ideal for sites that want to use team member data within templates, Beaver Themer or blocks, but don't want individual team member pages.

If you'd like to enable public team member pages and archives, you can use this filter in a custom plugin, mu-plugin, or your theme's functions.php file:

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

After adding either of these filters, remember to flush permalinks by visiting **Settings > Permalinks** in your WordPress admin.

## Displaying Team Members

### Custom Query Method

If not using a page builder, use a custom query like this:

```php
$args = [
	'post_type' => 'weave_team',
	'posts_per_page' => -1,
	'orderby' => 'menu_order',
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
		echo '<div class="team-member">';
		echo '<h3>' . get_the_title() . '</h3>';
		if ($position) echo '<p class="position">' . esc_html($position) . '</p>';
		// ... more display code
		echo '</div>';
	endwhile;
	wp_reset_postdata();
endif;
```

### Using with Beaver Builder / Page Builders

This plugin works excellently with Beaver Builder. When setting up a Themer layout:

- Use **"Post Title"** for the team member's name
- **"Featured Image"** for their photo
- **"Post Content"** for their biography
- **"Post Custom Field"** for position, qualifications, phone, and email

**Field names for "Post Custom Field":**
- `_weave_team_position`
- `_weave_team_qualification`
- `_weave_team_phone`
- `_weave_team_email`

**Taxonomies for "Taxonomy Term":**
- `weave_team_location`
- `weave_team_role`

### Beaver Themer Shortcodes

For Beaver Themer layouts, you can use these shortcodes to display team member fields:

```
[wpbb post:custom_field key='_weave_team_position']
[wpbb post:custom_field key='_weave_team_qualification']
[wpbb post:custom_field key='_weave_team_phone'] 
[wpbb post:custom_field key='_weave_team_email']
```

You can also add formatting around these fields:

```
<div class="team-position">
	<strong>Position:</strong> [wpbb post:custom_field key='_weave_team_position']
</div>

<div class="team-contact">
	<strong>Email:</strong> <a href="mailto:[wpbb post:custom_field key='_weave_team_email']">[wpbb post:custom_field key='_weave_team_email']</a>
	<strong>Phone:</strong> <a href="tel:[wpbb post:custom_field key='_weave_team_phone']">[wpbb post:custom_field key='_weave_team_phone']</a>
</div>
```

## Customisation

The Weave Team Members plugin is designed to be highly customisable without editing the core plugin files. This allows you to adapt it to different projects while maintaining the ability to update the plugin in the future.

**Important:** All customisations should be added via WordPress filters and actions in a custom plugin, mu-plugin (recommended), or theme functions.php file.

### Customising Fields

You can enable/disable fields, rename them, or change their placeholders using the `weave_team_field_settings` filter.

#### Disabling Fields

To disable specific fields (like Phone and Email):

```php
/**
 * Disable the phone and email fields
 */
add_filter('weave_team_field_settings', function($field_settings) {
	// Disable phone field completely
	$field_settings['phone']['enabled'] = false;
	
	// Disable email field completely
	$field_settings['email']['enabled'] = false;
	
	return $field_settings;
});
```

#### Renaming Fields

To rename fields while keeping them enabled:

```php
/**
 * Rename fields for a specific project
 */
add_filter('weave_team_field_settings', function($field_settings) {
	// Change "Position" to "Job Title"
	$field_settings['position']['label'] = 'Job Title:';
	
	// Change "Qualification" to "Certifications"
	$field_settings['qualification']['label'] = 'Certifications:';
	
	return $field_settings;
});
```

#### Changing Placeholders

To customise the placeholder text in fields:

```php
/**
 * Change field placeholders
 */
add_filter('weave_team_field_settings', function($field_settings) {
	// Update placeholders to be more specific
	$field_settings['position']['placeholder'] = 'Enter job title or role...';
	$field_settings['email']['placeholder'] = 'Public email address (will be visible on website)';
	
	return $field_settings;
});
```

### Customising Taxonomies

You can enable/disable taxonomies, rename them, or change their slugs using the `weave_team_taxonomy_settings` filter.

#### Disabling Taxonomies

To disable the Location taxonomy:

```php
/**
 * Disable the location taxonomy
 */
add_filter('weave_team_taxonomy_settings', function($taxonomy_settings) {
	// Disable location taxonomy completely
	$taxonomy_settings['location']['enabled'] = false;
	
	return $taxonomy_settings;
});
```

#### Renaming Taxonomies

To rename the Job Role taxonomy to "Departments":

```php
/**
 * Rename taxonomies for a specific project
 */
add_filter('weave_team_taxonomy_settings', function($taxonomy_settings) {
	// Change "Job Roles" to "Departments"
	$taxonomy_settings['role']['label'] = 'Departments';
	$taxonomy_settings['role']['singular'] = 'Department';
	
	return $taxonomy_settings;
});
```

#### Changing Taxonomy Slugs

To change the URL slug for taxonomies:

```php
/**
 * Change taxonomy slugs
 */
add_filter('weave_team_taxonomy_settings', function($taxonomy_settings) {
	// Change the URL slugs
	$taxonomy_settings['role']['slug'] = 'department';
	$taxonomy_settings['location']['slug'] = 'office-location';
	
	return $taxonomy_settings;
});
```

### Complete Customisation Example

Here's a comprehensive example that customises both fields and taxonomies:

```php
/**
 * Customise the Team Members Plugin for a client site
 */
function my_customise_team_plugin() {
	// Customise fields
	add_filter('weave_team_field_settings', function($field_settings) {
		// Rename position
		$field_settings['position']['label'] = 'Job Title:';
		$field_settings['position']['placeholder'] = 'Enter job title...';
		
		// Rename qualification 
		$field_settings['qualification']['label'] = 'Expertise:';
		$field_settings['qualification']['placeholder'] = 'Areas of expertise...';
		
		// Disable phone
		$field_settings['phone']['enabled'] = false;
		
		// Keep email with default settings
		
		return $field_settings;
	});
	
	// Customise taxonomies
	add_filter('weave_team_taxonomy_settings', function($taxonomy_settings) {
		// Keep location but rename it
		$taxonomy_settings['location']['label'] = 'Offices';
		$taxonomy_settings['location']['singular'] = 'Office';
		$taxonomy_settings['location']['slug'] = 'office';
		
		// Change role to department
		$taxonomy_settings['role']['label'] = 'Departments';
		$taxonomy_settings['role']['singular'] = 'Department';
		$taxonomy_settings['role']['slug'] = 'department';
		
		return $taxonomy_settings;
	});
}
add_action('init', 'my_customise_team_plugin', 5); // Add early to run before CPT registration
```

### Adding New Fields

To add a completely new field (like LinkedIn):

```php
/**
 * Add a new LinkedIn field
 */
add_filter('weave_team_field_settings', function($field_settings) {
	$field_settings['linkedin'] = [
		'enabled' => true,
		'label' => 'LinkedIn Profile:',
		'type' => 'url',
		'placeholder' => 'Enter LinkedIn URL...'
	];
	return $field_settings;
});

// Register it for the REST API
add_action('rest_api_init', function() {
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

### Important Customisation Notes

1. **Add customisations to a custom plugin or mu-plugin** (recommended), or to your theme's `functions.php` file
2. **Filters should be added before CPT registration** (priority < 10)
3. **Database field names remain consistent** (`_weave_team_position`, etc.) regardless of UI label changes
4. **Disabling a field will:**
   - Remove it from the meta box in the editor
   - Remove it from the Team Member block
   - Remove it from admin columns
   - Stop it from being saved to the database when the post is updated

The Team Members plugin maintains consistent database field names even when the UI labels are customised, ensuring compatibility with tools like Beaver Builder Themer.

## Troubleshooting

### Common Issues

- **Fields not showing?** Check if they are enabled in your filter settings
- **Block missing?** Make sure JavaScript files are loading correctly and the block is registered
- **Taxonomy terms not appearing?** Confirm the taxonomy is enabled via filters
- **Job Roles not selectable on new posts?** This was a known issue that has been fixed in recent versions

### Debug Information

For debugging, add this to your theme's functions.php or a custom plugin:

```php
add_action('admin_footer', function() {
	if (get_current_screen()->post_type !== 'weave_team') return;
	echo '<div style="display:none;"><pre>';
	echo "Field Settings:\n";
	print_r(weave_team_get_field_settings());
	echo "\nTaxonomy Settings:\n";
	print_r(weave_team_get_taxonomy_settings());
	echo '</pre></div>';
});
```

### Getting Help

If you're still having issues:
1. Check the WordPress debug log for any PHP errors
2. Verify your customisation filters are running at the right priority
3. Test with a default WordPress theme to rule out theme conflicts
4. Ensure you're using the latest version of the plugin
5. Visit the GitHub repository (link available on the plugin page) to report issues or check for updates

## Technical Details

### Database Fields

The plugin stores data in these meta fields:
- `_weave_team_position` - Position/Job Title
- `_weave_team_qualification` - Qualifications/Certifications  
- `_weave_team_phone` - Phone Number
- `_weave_team_email` - Email Address

### Taxonomies

- `weave_team_location` - Team member locations
- `weave_team_role` - Job roles/positions

### Post Type

- **Name:** `weave_team`
- **Supports:** Title, Editor, Thumbnail, Custom Fields, Page Attributes
- **REST API:** Enabled
- **Public:** Disabled by default (use `weave_team_public` filter to enable)

### GitHub Integration

The plugin includes automatic update functionality and adds a "GitHub" link to the plugin actions on the WordPress plugins page, providing easy access to the source code and issue tracking.

## Credits

Developed by [Weave Digital Studio](https://weave.co.nz)

## License

This plugin is licensed under the GPL v2 or later.

