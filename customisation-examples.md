# Customising Weave Team Members Module

The Weave Team Members CPT module is designed to be highly customisable without editing the core plugin files. This allows you to adapt it to different projects while maintaining the ability to update the plugin in the future.

## Customising Fields

You can enable/disable fields, rename them, or change their placeholders using the `weave_team_field_settings` filter.

### Disabling Fields

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

### Renaming Fields

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

### Changing Placeholders

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

## Customising Taxonomies

You can enable/disable taxonomies, rename them, or change their slugs using the `weave_team_taxonomy_settings` filter.

### Disabling Taxonomies

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

### Renaming Taxonomies

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

### Changing Taxonomy Slugs

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

## Complete Example

Here's a comprehensive example that customises both fields and taxonomies:

```php
/**
 * Customise the Team Members Module for a client site
 */
function my_customise_team_module() {
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
add_action('init', 'my_customise_team_module', 5); // Add early to run before CPT registration
```


## Important Notes

1. Add these code snippets to your theme's `functions.php` file or a custom plugin.
2. The filters should ideally be added before the post type and taxonomies are registered (priority < 10).
3. The meta field names in the database remain the same (`_weave_team_position`, etc.) regardless of how you rename the fields in the UI.
4. Disabling a field will:
   - Remove it from the meta box in the editor
   - Remove it from the Team Member block
   - Remove it from admin columns
   - Stop it from being saved to the database when the post is updated

## Beaver Builder Integration

When using field connections in Beaver Builder Themer, you'll always use the original meta field names regardless of UI labels:

| Field | Meta Key for Beaver Themer |
|-------|----------------------------|
| Position | `_weave_team_position` |
| Qualification | `_weave_team_qualification` |
| Phone | `_weave_team_phone` |
| Email | `_weave_team_email` |

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



The Team Members module maintains consistent database field names even when the UI labels are customised, ensuring compatibility with tools like Beaver Builder Themer.

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

