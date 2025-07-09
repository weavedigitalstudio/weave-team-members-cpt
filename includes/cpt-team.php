<?php
/**
 * Team Custom Post Type
 *
 * @package    Team_Member_Module
 * @version    1.0.2
 * @since      1.0.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Get field settings - which fields should be displayed and their labels
 * 
 * @return array Array of field settings with enabled status and labels
 */
function weave_team_get_field_settings() {
	$default_settings = array(
		'position' => array(
			'enabled' => true,
			'label' => __('Position:', 'weave-team-members-cpt'),
			'type' => 'text',
			'placeholder' => __('Enter position...', 'weave-team-members-cpt')
		),
		'qualification' => array(
			'enabled' => true,
			'label' => __('Qualification:', 'weave-team-members-cpt'),
			'type' => 'text',
			'placeholder' => __('Enter qualifications...', 'weave-team-members-cpt')
		),
		'phone' => array(
			'enabled' => true,
			'label' => __('Phone:', 'weave-team-members-cpt'),
			'type' => 'text',
			'placeholder' => __('Enter phone number...', 'weave-team-members-cpt')
		),
		'email' => array(
			'enabled' => true,
			'label' => __('Email:', 'weave-team-members-cpt'),
			'type' => 'email',
			'placeholder' => __('Enter email address...', 'weave-team-members-cpt')
		)
	);
	
	return apply_filters('weave_team_field_settings', $default_settings);
}

/**
 * Get taxonomy settings - which taxonomies should be registered
 * 
 * @return array Array of taxonomy settings with enabled status and labels
 */
function weave_team_get_taxonomy_settings() {
	$default_settings = array(
		'location' => array(
			'enabled' => true,
			'label' => __('Locations', 'weave-team-members-cpt'),
			'singular' => __('Location', 'weave-team-members-cpt'),
			'slug' => 'team-location',
			'hierarchical' => true
		),
		'role' => array(
			'enabled' => true,
			'label' => __('Job Roles', 'weave-team-members-cpt'),
			'singular' => __('Job Role', 'weave-team-members-cpt'),
			'slug' => 'job-role',
			'hierarchical' => true
		)
	);
	
	return apply_filters('weave_team_taxonomy_settings', $default_settings);
}

/**
 * Register Team Custom Post Type
 */
if (!function_exists('weave_cpt_register_team')) {
	function weave_cpt_register_team() {
		$labels = array(
			'name'                  => _x('Team Members', 'Post type general name', 'weave-team-members-cpt'),
			'singular_name'         => _x('Team Member', 'Post type singular name', 'weave-team-members-cpt'),
			'menu_name'             => _x('Team Members', 'Admin Menu text', 'weave-team-members-cpt'),
			'name_admin_bar'        => _x('Team Member', 'Add New on Toolbar', 'weave-team-members-cpt'),
			'add_new'               => __('Add Team Member', 'weave-team-members-cpt'),
			'add_new_item'          => __('Add New Team Member', 'weave-team-members-cpt'),
			'new_item'              => __('New Team Member', 'weave-team-members-cpt'),
			'edit_item'             => __('Edit Team Member', 'weave-team-members-cpt'),
			'view_item'             => __('View Team Members', 'weave-team-members-cpt'),
			'all_items'             => __('All Team', 'weave-team-members-cpt'),
			'search_items'          => __('Search Team', 'weave-team-members-cpt'),
			'not_found'             => __('No team members found.', 'weave-team-members-cpt'),
			'not_found_in_trash'    => __('No team members found in Trash.', 'weave-team-members-cpt'),
			'featured_image'        => __('Team Member Image', 'weave-team-members-cpt'),
			'set_featured_image'    => __('Set team member image', 'weave-team-members-cpt'),
			'remove_featured_image' => __('Remove team member image', 'weave-team-members-cpt'),
			'use_featured_image'    => __('Use as team member image', 'weave-team-members-cpt'),
		);

 		// Default settings optimised for page builders while maintaining privacy
		$args = array(
			'labels'             => $labels,
			'public'             => true,              // Allow querying for page builders
			'publicly_queryable' => true,              // Allow querying for page builders
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 6,
			'query_var'          => true,
			'rewrite'            => false,             // No individual post URLs
			'capability_type'    => 'post',
			'has_archive'        => false,             // No archive page
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-groups',
			'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes'),
			'show_in_rest'       => true,
			'show_in_nav_menus'  => false,             // Don't show in navigation menus
			'exclude_from_search'=> true,              // Exclude from search results
		);
	
		// Allow additional filtering of all args
		$args = apply_filters('weave_team_post_type_args', $args);
		
		register_post_type('weave_team', $args);
	}
	add_action('init', 'weave_cpt_register_team', 0);
}
/**
 * Register Team Location Taxonomy
 */
if (!function_exists('weave_tax_register_locations')) {
	function weave_tax_register_locations() {
		$taxonomy_settings = weave_team_get_taxonomy_settings();
		
		// Skip registration if taxonomy is disabled
		if (!isset($taxonomy_settings['location']['enabled']) || !$taxonomy_settings['location']['enabled']) {
			return;
		}
		
		// Get label settings
		$tax_label = $taxonomy_settings['location']['label'];
		$tax_singular = $taxonomy_settings['location']['singular'];
		$tax_slug = $taxonomy_settings['location']['slug'];
		$hierarchical = !empty($taxonomy_settings['location']['hierarchical']);
		
		$labels = array(
			'name'              => $tax_label,
			'singular_name'     => $tax_singular,
			'menu_name'         => $tax_singular,
			'all_items'         => sprintf(__('All %s', 'weave-team-members-cpt'), $tax_label),
			'edit_item'         => sprintf(__('Edit %s', 'weave-team-members-cpt'), $tax_singular),
			'update_item'       => sprintf(__('Update %s', 'weave-team-members-cpt'), $tax_singular),
			'add_new_item'      => sprintf(__('Add New %s', 'weave-team-members-cpt'), $tax_singular),
			'new_item_name'     => sprintf(__('New %s Name', 'weave-team-members-cpt'), $tax_singular),
			'search_items'      => sprintf(__('Search %s', 'weave-team-members-cpt'), $tax_label),
			'parent_item'       => sprintf(__('Parent %s', 'weave-team-members-cpt'), $tax_singular),
			'parent_item_colon' => sprintf(__('Parent %s:', 'weave-team-members-cpt'), $tax_singular),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => $hierarchical,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => $tax_slug),
		);

		register_taxonomy('weave_team_location', array('weave_team'), $args);
	}
	add_action('init', 'weave_tax_register_locations', 0);
}

/**
 * Register Team Role Taxonomy
 */
if (!function_exists('weave_tax_register_roles')) {
	function weave_tax_register_roles() {
		$taxonomy_settings = weave_team_get_taxonomy_settings();
		
		// Skip registration if taxonomy is disabled
		if (!isset($taxonomy_settings['role']['enabled']) || !$taxonomy_settings['role']['enabled']) {
			return;
		}
		
		// Get label settings
		$tax_label = $taxonomy_settings['role']['label'];
		$tax_singular = $taxonomy_settings['role']['singular'];
		$tax_slug = $taxonomy_settings['role']['slug'];
		$hierarchical = !empty($taxonomy_settings['role']['hierarchical']);
		
		$labels = array(
			'name'              => $tax_label,
			'singular_name'     => $tax_singular,
			'menu_name'         => $tax_label,
			'all_items'         => sprintf(__('All %s', 'weave-team-members-cpt'), $tax_label),
			'edit_item'         => sprintf(__('Edit %s', 'weave-team-members-cpt'), $tax_singular),
			'update_item'       => sprintf(__('Update %s', 'weave-team-members-cpt'), $tax_singular),
			'add_new_item'      => sprintf(__('Add New %s', 'weave-team-members-cpt'), $tax_singular),
			'new_item_name'     => sprintf(__('New %s Name', 'weave-team-members-cpt'), $tax_singular),
			'search_items'      => sprintf(__('Search %s', 'weave-team-members-cpt'), $tax_label),
			'parent_item'       => sprintf(__('Parent %s', 'weave-team-members-cpt'), $tax_singular),
			'parent_item_colon' => sprintf(__('Parent %s:', 'weave-team-members-cpt'), $tax_singular),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => $hierarchical,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array('slug' => $tax_slug),
		);

		register_taxonomy('weave_team_role', array('weave_team'), $args);
	}
	add_action('init', 'weave_tax_register_roles', 0);
}

/**
 * Register REST API fields
 */
function weave_register_team_meta() {
	// Get field settings to know which fields are enabled
	$field_settings = weave_team_get_field_settings();
	
	$fields = array(
		'position' => '_weave_team_position',
		'qualification' => '_weave_team_qualification',
		'phone' => '_weave_team_phone',
		'email' => '_weave_team_email'
	);

	foreach ($fields as $field_name => $meta_key) {
		// Only register enabled fields
		if (!isset($field_settings[$field_name]) || $field_settings[$field_name]['enabled']) {
			register_rest_field(
				'weave_team',
				$field_name,
				array(
					'get_callback' => function($post) use ($meta_key) {
						return get_post_meta($post['id'], $meta_key, true);
					},
					'schema' => array(
						'type' => 'string',
						'description' => 'Team member ' . $field_name
					)
				)
			);
		}
	}
}
add_action('rest_api_init', 'weave_register_team_meta');

/**
 * Register post meta for the block editor
 */
function weave_register_team_meta_for_rest() {
	// Get field settings to know which fields are enabled
	$field_settings = weave_team_get_field_settings();
	
	$meta_fields = array(
		'_weave_team_position' => 'string',
		'_weave_team_qualification' => 'string',
		'_weave_team_phone' => 'string',
		'_weave_team_email' => 'string'
	);
	
	foreach ($meta_fields as $meta_key => $type) {
		// Get the field name without prefix
		$field_name = str_replace('_weave_team_', '', $meta_key);
		
		// Only register enabled fields
		if (!isset($field_settings[$field_name]) || $field_settings[$field_name]['enabled']) {
			register_post_meta('weave_team', $meta_key, array(
				'show_in_rest' => true,
				'single' => true,
				'type' => $type,
				'auth_callback' => function() {
					return current_user_can('edit_posts');
				}
			));
		}
	}
}
add_action('init', 'weave_register_team_meta_for_rest');

/**
 * Check if post has Team Member block
 */
function weave_post_has_team_member_block($post_id) {
	$post = get_post($post_id);
	if (!$post) {
		return false;
	}
	
	// Check if the post content contains our team member block
	return has_block('weave-digital/team-member', $post);
}

/**
 * Add a custom meta box for team member information
 * Only add if the post doesn't have the Team Member block to avoid duplication
 */
function weave_add_team_meta_boxes() {
	// Get the current post ID
	$post_id = get_the_ID();
	if (!$post_id) {
		global $post;
		$post_id = $post ? $post->ID : 0;
	}
	
	// Don't add meta box if the post has the Team Member block
	if ($post_id && weave_post_has_team_member_block($post_id)) {
		return;
	}
	
	// Don't add meta box if no fields are enabled
	$field_settings = weave_team_get_field_settings();
	$has_enabled_fields = false;
	
	foreach ($field_settings as $field) {
		if (!empty($field['enabled'])) {
			$has_enabled_fields = true;
			break;
		}
	}
	
	if (!$has_enabled_fields) {
		return;
	}
	
	add_meta_box(
		'weave_team_info',
		__('Team Member Information', 'weave-team-members-cpt'),
		'weave_team_info_callback',
		'weave_team',
		'side',
		'high'
	);
}
add_action('add_meta_boxes', 'weave_add_team_meta_boxes');

/**
 * Hide taxonomy meta boxes when using the Team Member block
 */
function weave_hide_taxonomy_meta_boxes() {
	// Get the current post ID
	$post_id = get_the_ID();
	if (!$post_id) {
		global $post;
		$post_id = $post ? $post->ID : 0;
	}
	
	// Hide taxonomy meta boxes if the post has the Team Member block
	if ($post_id && weave_post_has_team_member_block($post_id)) {
		remove_meta_box('weave_team_locationdiv', 'weave_team', 'side');
		remove_meta_box('weave_team_rolediv', 'weave_team', 'side');
		// Also remove the hierarchical versions
		remove_meta_box('weave_team_locationdiv', 'weave_team', 'normal');
		remove_meta_box('weave_team_rolediv', 'weave_team', 'normal');
	}
}
add_action('add_meta_boxes', 'weave_hide_taxonomy_meta_boxes', 20);

/**
 * Meta box callback function
 */
function weave_team_info_callback($post) {
	wp_nonce_field('weave_team_info_nonce', 'weave_team_info_nonce');
	
	// Get field settings
	$field_settings = weave_team_get_field_settings();
	
	foreach ($field_settings as $field => $settings) {
		// Skip disabled fields
		if (!$settings['enabled']) {
			continue;
		}
		
		$value = get_post_meta($post->ID, '_weave_team_' . $field, true);
		?>
		<p>
			<label for="weave_team_<?php echo $field; ?>"><?php echo $settings['label']; ?></label>
			<input type="<?php echo $settings['type']; ?>" 
				   id="weave_team_<?php echo $field; ?>" 
				   name="weave_team_<?php echo $field; ?>" 
				   value="<?php echo esc_attr($value); ?>" 
				   placeholder="<?php echo esc_attr($settings['placeholder']); ?>"
				   style="width: 100%;">
		</p>
		<?php
	}
}

/**
 * Save the meta box data
 */
function weave_save_team_meta($post_id) {
	if (!isset($_POST['weave_team_info_nonce']) || 
		!wp_verify_nonce($_POST['weave_team_info_nonce'], 'weave_team_info_nonce')) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	// Get field settings to know which fields are enabled
	$field_settings = weave_team_get_field_settings();
	
	$fields = array(
		'position' => 'sanitize_text_field',
		'qualification' => 'sanitize_text_field',
		'phone' => 'sanitize_text_field',
		'email' => 'sanitize_email'
	);

	foreach ($fields as $field => $sanitize_callback) {
		// Only process enabled fields
		if (isset($field_settings[$field]) && $field_settings[$field]['enabled']) {
			if (isset($_POST['weave_team_' . $field])) {
				$value = call_user_func($sanitize_callback, $_POST['weave_team_' . $field]);
				update_post_meta($post_id, '_weave_team_' . $field, $value);
			}
		}
	}
}
add_action('save_post_weave_team', 'weave_save_team_meta');

/**
 * Add custom columns to team admin list
 */
function weave_add_team_columns($columns) {
	// Get field settings to know which fields are enabled
	$field_settings = weave_team_get_field_settings();
	// Get taxonomy settings to know which taxonomies are enabled
	$taxonomy_settings = weave_team_get_taxonomy_settings();
	
	$new_columns = array();
	
	// Add checkbox first (if it exists)
	if (isset($columns['cb'])) {
		$new_columns['cb'] = $columns['cb'];
	}
	
	// Add featured image first (150px x 150px)
	$new_columns['featured_image'] = __('Image', 'weave-team-members-cpt');
	
	// Add title
	if (isset($columns['title'])) {
		$new_columns['title'] = $columns['title'];
	}
	
	// Add enabled fields
	if (isset($field_settings['position']) && $field_settings['position']['enabled']) {
		$new_columns['position'] = __('Position', 'weave-team-members-cpt');
	}
	
	if (isset($field_settings['qualification']) && $field_settings['qualification']['enabled']) {
		$new_columns['qualification'] = __('Qualification', 'weave-team-members-cpt');
	}
	
	// Add enabled taxonomies BEFORE date
	if (isset($taxonomy_settings['location']['enabled']) && $taxonomy_settings['location']['enabled']) {
		$new_columns['taxonomy-weave_team_location'] = $taxonomy_settings['location']['label'];
	}
	
	if (isset($taxonomy_settings['role']['enabled']) && $taxonomy_settings['role']['enabled']) {
		$new_columns['taxonomy-weave_team_role'] = $taxonomy_settings['role']['label'];
	}
	
	// Add remaining columns (date, etc.)
	foreach($columns as $key => $value) {
		if (!isset($new_columns[$key]) && 
			$key !== 'taxonomy-weave_team_location' && 
			$key !== 'taxonomy-weave_team_role') {
			$new_columns[$key] = $value;
		}
	}
	
	return $new_columns;
}
add_filter('manage_weave_team_posts_columns', 'weave_add_team_columns');

/**
 * Display team data in the custom columns
 */
function weave_display_team_columns($column, $post_id) {
	// Get field settings
	$field_settings = weave_team_get_field_settings();
	
	if ($column === 'featured_image') {
		$thumbnail_id = get_post_thumbnail_id($post_id);
		if ($thumbnail_id) {
			$thumbnail = wp_get_attachment_image($thumbnail_id, array(150, 150), true, array(
				'style' => 'width: 150px; height: 150px; object-fit: cover; border-radius: 4px;'
			));
			echo $thumbnail;
		} else {
			echo '<div style="width: 150px; height: 150px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">No Image</div>';
		}
		return;
	}
	
	$fields = array('position', 'qualification');
	
	if (in_array($column, $fields)) {
		// Only display enabled fields
		if (isset($field_settings[$column]) && $field_settings[$column]['enabled']) {
			$value = get_post_meta($post_id, '_weave_team_' . $column, true);
			echo !empty($value) ? esc_html($value) : '—';
		}
	}
}
add_action('manage_weave_team_posts_custom_column', 'weave_display_team_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function weave_sortable_team_columns($columns) {
	// Get field settings
	$field_settings = weave_team_get_field_settings();
	
	// Only make enabled fields sortable
	if (isset($field_settings['position']) && $field_settings['position']['enabled']) {
		$columns['position'] = 'position';
	}
	
	if (isset($field_settings['qualification']) && $field_settings['qualification']['enabled']) {
		$columns['qualification'] = 'qualification';
	}
	
	return $columns;
}
add_filter('manage_edit-weave_team_sortable_columns', 'weave_sortable_team_columns');

/**
 * Add sorting functionality to custom columns
 */
function weave_team_orderby($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}
	
	$orderby = $query->get('orderby');
	
	if ($query->get('post_type') === 'weave_team') {
		if ($orderby === 'position') {
			$query->set('meta_key', '_weave_team_position');
			$query->set('orderby', 'meta_value');
		}
		elseif ($orderby === 'qualification') {
			$query->set('meta_key', '_weave_team_qualification');
			$query->set('orderby', 'meta_value');
		}
	}
}
add_action('pre_get_posts', 'weave_team_orderby');

/**
 * Customize the title placeholder for Team post type
 */
function weave_team_change_title_text($title) {
	$screen = get_current_screen();
	
	if ('weave_team' == $screen->post_type) {
		$title = 'Enter team member full name here';
	}
	
	return $title;
}
add_filter('enter_title_here', 'weave_team_change_title_text');

/**
 * Auto-insert the Team Member block into new team posts
 */
function weave_auto_insert_team_member_block($post_id, $post) {
	// Ensure we have a valid post object
	if (!$post || !is_object($post)) {
		$post = get_post($post_id);
	}
	
	// Only proceed for our custom post type and new posts
	if (!$post || $post->post_type !== 'weave_team' || $post->post_content !== '') {
		return;
	}
	
	// Create block content
	$block_content = '<!-- wp:weave-digital/team-member /-->';
	
	// Update the post with our block
	wp_update_post(array(
		'ID' => $post->ID,
		'post_content' => $block_content,
	));
}
add_action('wp_insert_post', 'weave_auto_insert_team_member_block', 10, 2);

/**
 * Add a template for the Team Member post type
 */
function weave_register_team_member_template() {
	$post_type_object = get_post_type_object('weave_team');
	
	if ($post_type_object) {
		$post_type_object->template = array(
			array('weave-digital/team-member'),
			array('core/paragraph', array(
				'placeholder' => __('Add team member biography...', 'weave-team-members-cpt')
			))
		);
		
		// Lock the template so users can't move or delete the Team Member block
		$post_type_object->template_lock = 'insert';
	}
}
add_action('init', 'weave_register_team_member_template', 11); // Run after CPT registration

/**
 * Ensure Simple Page Ordering plugin compatibility
 */
function weave_team_enable_simple_page_ordering($sortable, $post_type) {
	if ($post_type === 'weave_team') {
		return true;
	}
	return $sortable;
}
add_filter('simple_page_ordering_is_sortable', 'weave_team_enable_simple_page_ordering', 10, 2);

/**
 * Include the Team Member block registration
 */
require_once WEAVE_TEAM_DIR . 'includes/blocks/team-member-block/index.php';