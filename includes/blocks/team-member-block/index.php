<?php
/**
 * Register Team Member Block
 *
 * @package    Weave_Digital
 * @subpackage Team_Module
 * @version    1.0.1
 * @since      1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Team Member Block Registration
 */
function weave_register_team_member_block() {
	// Verify that block dependencies are available
	if (!function_exists('register_block_type')) {
		return;
	}

	// Register block scripts and styles
	$js_path = WEAVE_TEAM_URL . '/includes/blocks/team-member-block/index.js';
	$css_path = WEAVE_TEAM_URL . '/includes/blocks/team-member-block/editor.css';
	
	wp_register_script(
		'weave-team-member-block',
		$js_path,
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-core-data'),
		filemtime(WEAVE_TEAM_DIR . '/includes/blocks/team-member-block/index.js')
	);
	
	wp_register_style(
		'weave-team-member-block-editor',
		$css_path,
		array(),
		filemtime(WEAVE_TEAM_DIR . '/includes/blocks/team-member-block/editor.css')
	);
	
	// Register the block
	register_block_type('weave-digital/team-member', array(
		'editor_script' => 'weave-team-member-block',
		'editor_style' => 'weave-team-member-block-editor',
		'render_callback' => 'weave_render_team_member_block',
		'attributes' => array(
			'qualification' => array(
				'type' => 'string',
				'default' => '',
			),
			'position' => array(
				'type' => 'string',
				'default' => '',
			),
			'phone' => array(
				'type' => 'string',
				'default' => '',
			),
			'email' => array(
				'type' => 'string',
				'default' => '',
			),
			'selectedLocation' => array(
				'type' => 'string',
				'default' => '',
			),
			'selectedRole' => array(
				'type' => 'string',
				'default' => '',
			),
			'featuredImageId' => array(
				'type' => 'number',
				'default' => 0,
			),
			'featuredImageUrl' => array(
				'type' => 'string',
				'default' => '',
			),
		),
	));
	
	// Load block data when editing a team member
	add_action('admin_enqueue_scripts', 'weave_load_team_member_block_data');
}
add_action('init', 'weave_register_team_member_block');

/**
 * Render callback for the Team Member block
 */
function weave_render_team_member_block($attributes, $content) {
	// This block is just for data entry, not display
	// Just return empty for admin blocks
	return '';
}

/**
 * Load block data from post meta when editing
 */
function weave_load_team_member_block_data() {
	global $post;
	
	// Only proceed for our custom post type on edit screens
	if (!is_admin() || empty($post) || $post->post_type !== 'weave_team') {
		return;
	}
	
	// Get meta values
	$meta_values = array(
		'position' => get_post_meta($post->ID, '_weave_team_position', true),
		'qualification' => get_post_meta($post->ID, '_weave_team_qualification', true),
		'phone' => get_post_meta($post->ID, '_weave_team_phone', true),
		'email' => get_post_meta($post->ID, '_weave_team_email', true)
	);
	
	// Get taxonomy terms
	$taxonomy_settings = weave_team_get_taxonomy_settings();
	
	if (!empty($taxonomy_settings['location']['enabled'])) {
		$location_terms = wp_get_object_terms($post->ID, 'weave_team_location');
		$location_id = !empty($location_terms) ? $location_terms[0]->term_id : '';
		$meta_values['selectedLocation'] = $location_id;
	}
	
	if (!empty($taxonomy_settings['role']['enabled'])) {
		$role_terms = wp_get_object_terms($post->ID, 'weave_team_role');
		$role_id = !empty($role_terms) ? $role_terms[0]->term_id : '';
		$meta_values['selectedRole'] = $role_id;
	}
	
	// Add settings for fields and taxonomies
	$meta_values['fieldSettings'] = weave_team_get_field_settings();
	$meta_values['taxonomySettings'] = $taxonomy_settings;
	
	// Get featured image info
	$featured_image_id = get_post_thumbnail_id($post->ID);
	if ($featured_image_id) {
		$meta_values['featuredImageId'] = $featured_image_id;
		$featured_image_url = wp_get_attachment_image_src($featured_image_id, 'full');
		if ($featured_image_url) {
			$meta_values['featuredImageUrl'] = $featured_image_url[0];
		}
	}
	
	// Enqueue the script with the data
	wp_localize_script('weave-team-member-block', 'weaveTeamMemberData', $meta_values);
}

/**
 * Save block data to post meta when the post is saved
 */
function weave_save_team_member_block_data($post_id, $post) {
	if ($post->post_type !== 'weave_team') {
		return;
	}

	$blocks = parse_blocks($post->post_content);
	foreach ($blocks as $block) {
		if ($block['blockName'] === 'weave-digital/team-member') {
			$attrs = $block['attrs'];

			// Save text fields
			$fields = array('position', 'qualification', 'phone', 'email');
			foreach ($fields as $field) {
				if (isset($attrs[$field])) {
					update_post_meta($post_id, '_weave_team_' . $field, sanitize_text_field($attrs[$field]));
				}
			}

			// Save taxonomies
			if (!empty($attrs['selectedLocation'])) {
				wp_set_object_terms($post_id, intval($attrs['selectedLocation']), 'weave_team_location');
			}
			
			if (!empty($attrs['selectedRole'])) {
				wp_set_object_terms($post_id, intval($attrs['selectedRole']), 'weave_team_role');
			}

			// Save featured image
			if (isset($attrs['featuredImageId']) && $attrs['featuredImageId'] > 0) {
				set_post_thumbnail($post_id, $attrs['featuredImageId']);
			} elseif (isset($attrs['featuredImageId']) && $attrs['featuredImageId'] === 0) {
				delete_post_thumbnail($post_id);
			}

			break; // Process only the first instance of the block
		}
	}
}
add_action('save_post', 'weave_save_team_member_block_data', 10, 2);
