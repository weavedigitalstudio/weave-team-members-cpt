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
	
	// Only proceed on admin screens
	if (!is_admin()) {
		return;
	}
	
	// Get current screen to check post type
	$screen = get_current_screen();
	if (!$screen || $screen->post_type !== 'weave_team') {
		return;
	}
	
	// Also check if we're on the block editor for this post type
	if (!in_array($screen->base, array('post', 'post-new'))) {
		return;
	}
	
	// For new posts, $post might be empty, so handle that case
	$post_id = !empty($post) ? $post->ID : 0;
	
	// Get meta values (empty for new posts)
	$meta_values = array(
		'position' => $post_id ? get_post_meta($post_id, '_weave_team_position', true) : '',
		'qualification' => $post_id ? get_post_meta($post_id, '_weave_team_qualification', true) : '',
		'phone' => $post_id ? get_post_meta($post_id, '_weave_team_phone', true) : '',
		'email' => $post_id ? get_post_meta($post_id, '_weave_team_email', true) : ''
	);
	
	// Get taxonomy terms (empty for new posts)
	$taxonomy_settings = weave_team_get_taxonomy_settings();
	
	if (!empty($taxonomy_settings['location']['enabled'])) {
		$location_terms = $post_id ? wp_get_object_terms($post_id, 'weave_team_location') : array();
		$location_id = '';
		
		if (!is_wp_error($location_terms) && !empty($location_terms) && isset($location_terms[0]->term_id)) {
			$location_id = $location_terms[0]->term_id;
		}
		
		$meta_values['selectedLocation'] = $location_id;
	}
	
	if (!empty($taxonomy_settings['role']['enabled'])) {
		$role_terms = $post_id ? wp_get_object_terms($post_id, 'weave_team_role') : array();
		$role_id = '';
		
		if (!is_wp_error($role_terms) && !empty($role_terms) && isset($role_terms[0]->term_id)) {
			$role_id = $role_terms[0]->term_id;
		}
		
		$meta_values['selectedRole'] = $role_id;
	}
	
	// Always add settings for fields and taxonomies (critical for filtering)
	$meta_values['fieldSettings'] = weave_team_get_field_settings();
	$meta_values['taxonomySettings'] = $taxonomy_settings;
	
	// Get featured image info (none for new posts)
	$featured_image_id = $post_id ? get_post_thumbnail_id($post_id) : 0;
	if ($featured_image_id) {
		$meta_values['featuredImageId'] = $featured_image_id;
		$featured_image_url = wp_get_attachment_image_src($featured_image_id, 'full');
		if ($featured_image_url) {
			$meta_values['featuredImageUrl'] = $featured_image_url[0];
		}
	}
	
	// Always enqueue the script with settings data - critical for field filtering to work
	// Ensure the script is enqueued before localizing data
	if (wp_script_is('weave-team-member-block', 'registered')) {
		wp_enqueue_script('weave-team-member-block');
		wp_enqueue_style('weave-team-member-block-editor');
		wp_localize_script('weave-team-member-block', 'weaveTeamMemberData', $meta_values);
	}
}

/**
 * Save block data to post meta when the post is saved
 */
function weave_save_team_member_block_data($post_id, $post) {
	if ($post->post_type !== 'weave_team') {
		return;
	}

	// Skip if this is an autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Check if user has permission to edit the post
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	$blocks = parse_blocks($post->post_content);
	foreach ($blocks as $block) {
		if ($block['blockName'] === 'weave-digital/team-member') {
			$attrs = $block['attrs'];
			
			// Get field settings to check which fields are enabled
			$field_settings = weave_team_get_field_settings();
			$taxonomy_settings = weave_team_get_taxonomy_settings();

			// Save text fields only if they're enabled
			$fields = array('position', 'qualification', 'phone', 'email');
			foreach ($fields as $field) {
				if (isset($attrs[$field]) && 
					isset($field_settings[$field]) && 
					$field_settings[$field]['enabled']) {
					
					// Sanitize based on field type
					if ($field === 'email') {
						$value = sanitize_email($attrs[$field]);
					} else {
						$value = sanitize_text_field($attrs[$field]);
					}
					
					update_post_meta($post_id, '_weave_team_' . $field, $value);
				}
			}

			// Save taxonomies only if they're enabled
			if (isset($attrs['selectedLocation']) && 
				!empty($attrs['selectedLocation']) && 
				isset($taxonomy_settings['location']) && 
				$taxonomy_settings['location']['enabled']) {
				
				$location_id = intval($attrs['selectedLocation']);
				
				// Verify the term exists before setting it
				if ($location_id > 0 && term_exists($location_id, 'weave_team_location')) {
					$result = wp_set_object_terms($post_id, $location_id, 'weave_team_location');
					if (is_wp_error($result)) {
						error_log('Failed to set location term for post ' . $post_id . ': ' . $result->get_error_message());
					}
				}
			} elseif (isset($attrs['selectedLocation']) && 
					  empty($attrs['selectedLocation']) && 
					  isset($taxonomy_settings['location']) && 
					  $taxonomy_settings['location']['enabled']) {
				// Clear the taxonomy if empty value is set
				$result = wp_set_object_terms($post_id, array(), 'weave_team_location');
				if (is_wp_error($result)) {
					error_log('Failed to clear location terms for post ' . $post_id . ': ' . $result->get_error_message());
				}
			}
			
			if (isset($attrs['selectedRole']) && 
				!empty($attrs['selectedRole']) && 
				isset($taxonomy_settings['role']) && 
				$taxonomy_settings['role']['enabled']) {
				
				$role_id = intval($attrs['selectedRole']);
				
				// Verify the term exists before setting it
				if ($role_id > 0 && term_exists($role_id, 'weave_team_role')) {
					$result = wp_set_object_terms($post_id, $role_id, 'weave_team_role');
					if (is_wp_error($result)) {
						error_log('Failed to set role term for post ' . $post_id . ': ' . $result->get_error_message());
					}
				}
			} elseif (isset($attrs['selectedRole']) && 
					  empty($attrs['selectedRole']) && 
					  isset($taxonomy_settings['role']) && 
					  $taxonomy_settings['role']['enabled']) {
				// Clear the taxonomy if empty value is set
				$result = wp_set_object_terms($post_id, array(), 'weave_team_role');
				if (is_wp_error($result)) {
					error_log('Failed to clear role terms for post ' . $post_id . ': ' . $result->get_error_message());
				}
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

/**
 * Sync taxonomy changes from sidebar to block
 * This ensures that if someone uses the taxonomy meta boxes, the block stays in sync
 */
function weave_sync_taxonomy_to_block($post_id) {
	// Only process our post type
	if (get_post_type($post_id) !== 'weave_team') {
		return;
	}
	
	// Skip if this is an autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// Check if user has permission to edit the post
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Only sync if the post has the Team Member block
	if (!weave_post_has_team_member_block($post_id)) {
		return;
	}

	$post = get_post($post_id);
	if (!$post) {
		return;
	}

	// Get current taxonomy terms
	$taxonomy_settings = weave_team_get_taxonomy_settings();
	$needs_update = false;
	$blocks = parse_blocks($post->post_content);
	
	foreach ($blocks as $block_index => $block) {
		if ($block['blockName'] === 'weave-digital/team-member') {
			$attrs = $block['attrs'];
			
			// Sync location taxonomy
			if (isset($taxonomy_settings['location']) && $taxonomy_settings['location']['enabled']) {
				$location_terms = wp_get_object_terms($post_id, 'weave_team_location');
				$location_id = '';
				
				if (!is_wp_error($location_terms) && !empty($location_terms) && isset($location_terms[0]->term_id)) {
					$location_id = strval($location_terms[0]->term_id);
				}
				
				if (!isset($attrs['selectedLocation']) || $attrs['selectedLocation'] !== $location_id) {
					$attrs['selectedLocation'] = $location_id;
					$needs_update = true;
				}
			}
			
			// Sync role taxonomy
			if (isset($taxonomy_settings['role']) && $taxonomy_settings['role']['enabled']) {
				$role_terms = wp_get_object_terms($post_id, 'weave_team_role');
				$role_id = '';
				
				if (!is_wp_error($role_terms) && !empty($role_terms) && isset($role_terms[0]->term_id)) {
					$role_id = strval($role_terms[0]->term_id);
				}
				
				if (!isset($attrs['selectedRole']) || $attrs['selectedRole'] !== $role_id) {
					$attrs['selectedRole'] = $role_id;
					$needs_update = true;
				}
			}
			
			// Update the block if needed
			if ($needs_update) {
				$blocks[$block_index]['attrs'] = $attrs;
				$updated_content = serialize_blocks($blocks);
				
				// Remove the hook to prevent infinite loop
				remove_action('save_post', 'weave_sync_taxonomy_to_block', 15);
				
				wp_update_post(array(
					'ID' => $post_id,
					'post_content' => $updated_content,
				));
				
				// Re-add the hook
				add_action('save_post', 'weave_sync_taxonomy_to_block', 15);
			}
			
			break; // Process only the first instance of the block
		}
	}
}
add_action('save_post', 'weave_sync_taxonomy_to_block', 15);
