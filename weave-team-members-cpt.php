<?php
/**
 * Plugin Name: Weave Team Members CPT
 * Plugin URI: https://github.com/weavedigitalstudio/weave-team-members-cpt
 * Description: A lightweight, modular Team Members CPT for Weave websites
 * Version: 1.0.0
 * Author: Weave Digital
 * Author URI: https://weave.co.nz
 * Text Domain: weave-team-members-cpt
 * Domain Path: /languages
 * License: GPL v2 or later
 */
/**
 * Team Member Module
 *
 * @package    Team_Member_Module
 * @version    1.0.0
 * @since      1.0.0
 * @author     Weave Digital
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Define constants
define('WEAVE_TEAM_VERSION', '1.0.0');
define('WEAVE_TEAM_DIR', plugin_dir_path(__FILE__));
define('WEAVE_TEAM_URL', plugin_dir_url(__FILE__));

/**
 * Initialize the module
 */
function weave_team_init() {
	// Include the required files
	require_once WEAVE_TEAM_DIR . 'includes/cpt-team.php';
	
	// Register block assets
	add_action('init', 'weave_register_block_assets', 5);
}
add_action('plugins_loaded', 'weave_team_init');

/**
 * Register shared block assets
 */
function weave_register_block_assets() {
	if (!function_exists('register_block_type')) {
		return;
	}
	
	// Register editor CSS
	wp_register_style(
		'weave-block-editor-styles',
		WEAVE_TEAM_URL . 'assets/css/admin-styles.css',
		array('wp-edit-blocks'),
		WEAVE_TEAM_VERSION
	);
}

/**
 * Register the activation hook
 */
function weave_team_activate() {
	// Make sure our CPT is registered
	require_once WEAVE_TEAM_DIR . 'includes/cpt-team.php';
	
	// Register the CPT and taxonomies
	weave_cpt_register_team();
	weave_tax_register_locations();
	weave_tax_register_roles();
	
	// Flush rewrite rules to add our CPT
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'weave_team_activate');

/**
 * Register the deactivation hook
 */
function weave_team_deactivate() {
	// Flush rewrite rules to remove our CPT
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'weave_team_deactivate');
