<?php
/**
 * Weave Team Members CPT Setup and Customization
 *
 * @package YourTheme
 */

// Define the constants properly for theme context
if (!defined('WEAVE_TEAM_VERSION')) {
	define('WEAVE_TEAM_VERSION', '1.0.1');
	define('WEAVE_TEAM_DIR', trailingslashit(get_stylesheet_directory() . '/inc/post-types/weave-team-members-cpt'));
	define('WEAVE_TEAM_URL', trailingslashit(get_stylesheet_directory_uri() . '/inc/post-types/weave-team-members-cpt'));
}

/**
 * Include and customize Team Members CPT 
 */
function theme_setup_team_members() {
	// Include CPT file directly
	$cpt_file = WEAVE_TEAM_DIR . 'includes/cpt-team.php';
	if (file_exists($cpt_file)) {
		require_once $cpt_file;
		
		// Include the block file
		$block_file = WEAVE_TEAM_DIR . 'includes/blocks/team-member-block/index.php';
		if (file_exists($block_file)) {
			require_once $block_file;
		}
		
		// Register CSS
		add_action('admin_enqueue_scripts', function() {
			wp_register_style(
				'weave-block-editor-styles',
				WEAVE_TEAM_URL . 'assets/css/admin-styles.css',
				array('wp-edit-blocks'),
				WEAVE_TEAM_VERSION
			);
		});
	}
}
add_action('init', 'theme_setup_team_members', 5);

/**
 * Customize Team taxonomies
 */
function theme_customize_team_taxonomies($taxonomy_settings) {
	$taxonomy_settings['location']['enabled'] = false;
	$taxonomy_settings['role']['label'] = 'Groups';
	$taxonomy_settings['role']['singular'] = 'Group';
	$taxonomy_settings['role']['slug'] = 'team-group';
	return $taxonomy_settings;
}
add_filter('weave_team_taxonomy_settings', 'theme_customize_team_taxonomies', 5);

/**
 * Customize Team fields
 */
function theme_customize_team_fields($field_settings) {
	$field_settings['qualification']['enabled'] = false;
	$field_settings['phone']['enabled'] = false;
	$field_settings['email']['enabled'] = false;
	return $field_settings;
}
add_filter('weave_team_field_settings', 'theme_customize_team_fields', 5);

/**
 * Ensure only the enabled taxonomies are registered
 */
function theme_register_team_taxonomies_explicitly() {
	// Get the taxonomy settings with our filters applied
	$taxonomy_settings = apply_filters('weave_team_taxonomy_settings', array(
		'location' => array(
			'enabled' => true,
			'label' => 'Locations',
			'singular' => 'Location',
			'slug' => 'team-location',
			'hierarchical' => true
		),
		'role' => array(
			'enabled' => true,
			'label' => 'Job Roles',
			'singular' => 'Job Role',
			'slug' => 'job-role',
			'hierarchical' => true
		)
	));
	
	// Only register the role taxonomy (which should be renamed to Groups by our filter)
	if ($taxonomy_settings['role']['enabled'] && function_exists('weave_tax_register_roles')) {
		weave_tax_register_roles();
	}
	
	// Location should be disabled by our filter, but we'll check anyway
	if ($taxonomy_settings['location']['enabled'] && function_exists('weave_tax_register_locations')) {
		weave_tax_register_locations();
	}
}
add_action('init', 'theme_register_team_taxonomies_explicitly', 9);

/**
 * Ensure CPT gets registered explicitly
 */
function theme_register_team_cpt_explicitly() {
	if (function_exists('weave_cpt_register_team')) {
		weave_cpt_register_team();
	}
}
add_action('init', 'theme_register_team_cpt_explicitly', 10);