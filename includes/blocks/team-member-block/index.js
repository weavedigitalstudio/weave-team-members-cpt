/**
 * Team Member Block for Gutenberg with Field Filtering Support
 * 
 * @package    Weave_Digital
 * @subpackage Team_Module
 * @version    1.0.1
 */

/**
  * Team Member Block for Gutenberg with Featured Image Support
  */
 
 (function (wp) {
	 var registerBlockType = wp.blocks.registerBlockType;
	 var InspectorControls = wp.blockEditor.InspectorControls;
	 var useBlockProps = wp.blockEditor.useBlockProps;
	 var PanelBody = wp.components.PanelBody;
	 var TextControl = wp.components.TextControl;
	 var SelectControl = wp.components.SelectControl;
	 var Button = wp.components.Button;
	 var useSelect = wp.data.useSelect;
	 var useEntityProp = wp.coreData.useEntityProp;
	 var useEffect = wp.element.useEffect;
	 var createElement = wp.element.createElement;
	 var Fragment = wp.element.Fragment;
	 var useDispatch = wp.data.useDispatch;
 
	 registerBlockType('weave-digital/team-member', {
		 title: 'Team Member Info',
		 icon: 'groups',
		 category: 'common',
		 supports: {
			 html: false,
		 },
		 attributes: {
			 position: { type: 'string', default: '' },
			 qualification: { type: 'string', default: '' },
			 phone: { type: 'string', default: '' },
			 email: { type: 'string', default: '' },
			 selectedLocation: { type: 'string', default: '' },
			 selectedRole: { type: 'string', default: '' },
			 featuredImageId: { type: 'number', default: 0 },
			 featuredImageUrl: { type: 'string', default: '' }
		 },
 
		 edit: function (props) {
			 var attributes = props.attributes;
			 var setAttributes = props.setAttributes;
 
			 var blockProps = useBlockProps({
				 className: 'team-member-block',
			 });
			 
			 // Get current post ID
			 var postId = useSelect(function(select) {
				 return select('core/editor').getCurrentPostId();
			 }, []);
			 
			 // Get editPost function
			 var { editPost } = useDispatch('core/editor');
			 
			 // Create synced update functions for each field
			 function updatePosition(value) {
				 setAttributes({ position: value });
				 if (postId) {
					 editPost({ meta: { '_weave_team_position': value } });
				 }
			 }
			 
			 function updateQualification(value) {
				 setAttributes({ qualification: value });
				 if (postId) {
					 editPost({ meta: { '_weave_team_qualification': value } });
				 }
			 }
			 
			 function updatePhone(value) {
				 setAttributes({ phone: value });
				 if (postId) {
					 editPost({ meta: { '_weave_team_phone': value } });
				 }
			 }
			 
			 function updateEmail(value) {
				 setAttributes({ email: value });
				 if (postId) {
					 editPost({ meta: { '_weave_team_email': value } });
				 }
			 }
			 
			 function updateLocation(value) {
				 setAttributes({ selectedLocation: value });
				 if (postId && value) {
					 // This will need server-side handling for taxonomy terms
					 editPost({ meta: { '_weave_team_location': value } });
				 }
			 }
			 
			 function updateRole(value) {
				 setAttributes({ selectedRole: value });
				 if (postId && value) {
					 // This will need server-side handling for taxonomy terms
					 editPost({ meta: { '_weave_team_role': value } });
				 }
			 }

			 // Load settings if available
			 var fieldSettings = {};
			 var taxonomySettings = {};
 
			 if (window.weaveTeamMemberData !== undefined) {
				 if (attributes.position === '' && attributes.qualification === '' && attributes.phone === '' && attributes.email === '') {
					 setAttributes({
						 position: window.weaveTeamMemberData.position || '',
						 qualification: window.weaveTeamMemberData.qualification || '',
						 phone: window.weaveTeamMemberData.phone || '',
						 email: window.weaveTeamMemberData.email || '',
						 selectedLocation: window.weaveTeamMemberData.selectedLocation || '',
						 selectedRole: window.weaveTeamMemberData.selectedRole || ''
					 });
				 }
 
				 fieldSettings = window.weaveTeamMemberData.fieldSettings || {};
				 taxonomySettings = window.weaveTeamMemberData.taxonomySettings || {};
			 }
 
			 // Sync with WP Featured Image
			 const [postFeaturedImageId, setPostFeaturedImageId] = useEntityProp('postType', 'post', 'featured_media');
 
			 useEffect(() => {
				 if (postFeaturedImageId && attributes.featuredImageId !== postFeaturedImageId) {
					 setAttributes({ featuredImageId: postFeaturedImageId });
				 }
			 }, [postFeaturedImageId]);
 
			 var featuredImage = useSelect((select) => {
				 return attributes.featuredImageId ? select('core').getMedia(attributes.featuredImageId) : null;
			 }, [attributes.featuredImageId]);
 
			 useEffect(() => {
				 if (featuredImage && featuredImage.source_url) {
					 setAttributes({ featuredImageUrl: featuredImage.source_url });
				 }
			 }, [featuredImage]);
 
			 function selectImage() {
				 var frame = wp.media({
					 title: 'Select Team Member Image',
					 button: { text: 'Use this image' },
					 multiple: false,
					 library: { type: 'image' }
				 });
 
				 frame.on('select', function () {
					 var attachment = frame.state().get('selection').first().toJSON();
					 setAttributes({
						 featuredImageId: attachment.id,
						 featuredImageUrl: attachment.url
					 });
 
					 setPostFeaturedImageId(attachment.id);
				 });
 
				 frame.open();
			 }
 
			 function removeImage() {
				 setAttributes({
					 featuredImageId: 0,
					 featuredImageUrl: ''
				 });
 
				 setPostFeaturedImageId(0);
			 }
 
			 // Load taxonomy terms for dropdowns
			 var locations = useSelect((select) => {
				 if (!taxonomySettings.location || !taxonomySettings.location.enabled) {
					 return [];
				 }
				 return select('core').getEntityRecords('taxonomy', 'weave_team_location', { per_page: -1 });
			 }, [taxonomySettings]);
 
			 var jobRoles = useSelect((select) => {
				 if (!taxonomySettings.role || !taxonomySettings.role.enabled) {
					 return [];
				 }
				 return select('core').getEntityRecords('taxonomy', 'weave_team_role', { per_page: -1 });
			 }, [taxonomySettings]);
 
			 function getLocationOptions() {
				 var options = [{ label: 'Select Location', value: '' }];
				 if (locations) {
					 locations.forEach(function (location) {
						 options.push({ label: location.name, value: location.id.toString() });
					 });
				 }
				 return options;
			 }
 
			 function getRoleOptions() {
				 var options = [{ label: 'Select Job Role', value: '' }];
				 if (jobRoles) {
					 jobRoles.forEach(function (role) {
						 options.push({ label: role.name, value: role.id.toString() });
					 });
				 }
				 return options;
			 }
 
			 return createElement(
				 Fragment,
				 null,
				 createElement(
					 'div',
					 blockProps,
					 createElement('h3', { className: 'team-member-section-title' }, 'Team Member Information'),
 
					 // Featured Image Section
					 createElement(
						 'div',
						 { className: 'team-member-image-section', style: { marginBottom: '20px' } },
						 createElement('h3', { className: 'team-member-section-title' }, 'Profile Image'),
						 createElement(
							 'div',
							 { className: 'team-member-featured-image-container' },
							 attributes.featuredImageId > 0
								 ? [
									   createElement('img', {
										   src: attributes.featuredImageUrl,
										   alt: 'Team Member',
										   style: { maxWidth: '100%', height: 'auto' }
									   }),
									   createElement(
										   'div',
										   { className: 'team-member-image-buttons', style: { display: 'flex', gap: '8px' } },
										   [
											   createElement(Button, { isPrimary: true, onClick: selectImage }, 'Replace Image'),
											   createElement(Button, { isSecondary: true, onClick: removeImage }, 'Remove Image')
										   ]
									   )
								   ]
								 : createElement(Button, { isPrimary: true, onClick: selectImage }, 'Select Team Member Image')
						 )
					 ),
 
					 // Fields Section
					 createElement(
						 'div',
						 { className: 'team-member-fields' },
						 createElement(TextControl, {
							 label: 'Position',
							 value: attributes.position,
							 onChange: updatePosition
						 }),
						 createElement(TextControl, {
							 label: 'Qualification',
							 value: attributes.qualification,
							 onChange: updateQualification
						 }),
						 createElement(TextControl, {
							 label: 'Phone',
							 value: attributes.phone,
							 onChange: updatePhone
						 }),
						 createElement(TextControl, {
							 label: 'Email',
							 type: 'email',
							 value: attributes.email,
							 onChange: updateEmail
						 }),
						 createElement(SelectControl, {
							 label: 'Location',
							 value: attributes.selectedLocation,
							 options: getLocationOptions(),
							 onChange: updateLocation
						 }),
						 createElement(SelectControl, {
							 label: 'Job Role',
							 value: attributes.selectedRole,
							 options: getRoleOptions(),
							 onChange: updateRole
						 })
					 )
				 ),
				 createElement(
					 InspectorControls,
					 null,
					 createElement(
						 PanelBody,
						 { title: 'Team Member Details' },
						 createElement(
							 TextControl,
							 {
								 label: 'Position',
								 value: attributes.position,
								 onChange: updatePosition
							 }
						 ),
						 createElement(
							 TextControl,
							 {
								 label: 'Qualification',
								 value: attributes.qualification,
								 onChange: updateQualification
							 }
						 ),
						 createElement(
							 TextControl,
							 {
								 label: 'Phone',
								 value: attributes.phone,
								 onChange: updatePhone
							 }
						 ),
						 createElement(
							 TextControl,
							 {
								 label: 'Email',
								 type: 'email',
								 value: attributes.email,
								 onChange: updateEmail
							 }
						 ),
						 createElement(
							 SelectControl,
							 {
								 label: 'Location',
								 value: attributes.selectedLocation,
								 options: getLocationOptions(),
								 onChange: updateLocation
							 }
						 ),
						 createElement(
							 SelectControl,
							 {
								 label: 'Job Role',
								 value: attributes.selectedRole,
								 options: getRoleOptions(),
								 onChange: updateRole
							 }
						 )
					 )
				 )
			 );
		 },
 
		 save: function () {
			 return null;
		 }
	 });
 })(window.wp);