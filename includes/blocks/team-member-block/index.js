/**
 * Team Member Block for Gutenberg with Field Filtering Support
 * 
 * @package    Weave_Digital
 * @subpackage Team_Module
 * @version    1.0.2
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
				 
				 // Immediately sync with WordPress taxonomies for new posts
				 var dispatch = wp.data.dispatch('core/editor');
				 if (dispatch && dispatch.editPost) {
					 var currentTerms = value ? [parseInt(value)] : [];
					 dispatch.editPost({
						 weave_team_location: currentTerms
					 });
				 }
			 }
			 
			 function updateRole(value) {
				 setAttributes({ selectedRole: value });
				 
				 // Immediately sync with WordPress taxonomies for new posts
				 var dispatch = wp.data.dispatch('core/editor');
				 if (dispatch && dispatch.editPost) {
					 var currentTerms = value ? [parseInt(value)] : [];
					 dispatch.editPost({
						 weave_team_role: currentTerms
					 });
				 }
			 }

			 // Load settings if available, with fallbacks for new posts
			 var fieldSettings = {};
			 var taxonomySettings = {};
 
			 if (window.weaveTeamMemberData !== undefined) {
				 // Only load initial data once, don't override user changes
				 var shouldLoadInitialData = (
					 attributes.position === '' && 
					 attributes.qualification === '' && 
					 attributes.phone === '' && 
					 attributes.email === '' &&
					 attributes.selectedLocation === '' &&
					 attributes.selectedRole === ''
				 );
				 
				 				 if (shouldLoadInitialData) {
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
			 } else {
				 // Fallback for new posts - field settings should always come from PHP
				 // but this provides a safety fallback if PHP data isn't loaded
				 fieldSettings = {
					 position: { enabled: true, label: 'Position:', placeholder: 'Enter position...' },
					 qualification: { enabled: true, label: 'Qualification:', placeholder: 'Enter qualifications...' },
					 phone: { enabled: true, label: 'Phone:', placeholder: 'Enter phone number...' },
					 email: { enabled: true, label: 'Email:', placeholder: 'Enter email address...' }
				 };
				 taxonomySettings = {
					 location: { enabled: true, label: 'Locations', singular: 'Location' },
					 role: { enabled: true, label: 'Job Roles', singular: 'Job Role' }
				 };
			 }
 
			 // Sync with WP Featured Image
			 const [postFeaturedImageId, setPostFeaturedImageId] = useEntityProp('postType', 'weave_team', 'featured_media');
 
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
 
			 // Load taxonomy terms for dropdowns with loading states
			 var locations = useSelect((select) => {
				 if (!taxonomySettings.location || !taxonomySettings.location.enabled) {
					 return [];
				 }
				 try {
					 var locationTerms = select('core').getEntityRecords('taxonomy', 'weave_team_location', { per_page: -1 });
					 return locationTerms || [];
				 } catch (error) {
					 console.error('Error loading location terms:', error);
					 return [];
				 }
			 }, [taxonomySettings.location && taxonomySettings.location.enabled]);
 
			 var jobRoles = useSelect((select) => {
				 if (!taxonomySettings.role || !taxonomySettings.role.enabled) {
					 return [];
				 }
				 try {
					 var roleTerms = select('core').getEntityRecords('taxonomy', 'weave_team_role', { per_page: -1 });
					 return roleTerms || [];
				 } catch (error) {
					 console.error('Error loading role terms:', error);
					 return [];
				 }
			 }, [taxonomySettings.role && taxonomySettings.role.enabled]);
			 
			 // Get loading states
			 var locationsLoading = useSelect((select) => {
				 if (!taxonomySettings.location || !taxonomySettings.location.enabled) {
					 return false;
				 }
				 return select('core').isResolving('getEntityRecords', ['taxonomy', 'weave_team_location', { per_page: -1 }]);
			 }, [taxonomySettings.location && taxonomySettings.location.enabled]);
			 
			 var rolesLoading = useSelect((select) => {
				 if (!taxonomySettings.role || !taxonomySettings.role.enabled) {
					 return false;
				 }
				 return select('core').isResolving('getEntityRecords', ['taxonomy', 'weave_team_role', { per_page: -1 }]);
			 }, [taxonomySettings.role && taxonomySettings.role.enabled]);
 
			 function getLocationOptions() {
				 if (locationsLoading) {
					 return [{ label: 'Loading locations...', value: '', disabled: true }];
				 }
				 
				 var options = [{ label: 'Select Location', value: '' }];
				 if (locations && locations.length > 0) {
					 locations.forEach(function (location) {
						 options.push({ label: location.name, value: location.id.toString() });
					 });
				 } else if (!locationsLoading) {
					 options.push({ label: 'No locations available', value: '', disabled: true });
				 }
				 return options;
			 }
 
			 function getRoleOptions() {
				 var taxonomyLabel = (taxonomySettings.role && taxonomySettings.role.singular) ? taxonomySettings.role.singular : 'Job Role';
				 
				 if (rolesLoading) {
					 return [{ label: 'Loading ' + taxonomyLabel.toLowerCase() + 's...', value: '', disabled: true }];
				 }
				 
				 var options = [{ label: 'Select ' + taxonomyLabel, value: '' }];
				 if (jobRoles && jobRoles.length > 0) {
					 jobRoles.forEach(function (role) {
						 options.push({ label: role.name, value: role.id.toString() });
					 });
				 } else if (!rolesLoading) {
					 options.push({ label: 'No ' + taxonomyLabel.toLowerCase() + 's available', value: '', disabled: true });
				 }
				 
				 return options;
			 }

			 // Helper function to render enabled fields only
			 function renderField(fieldName, fieldType, label, value, onChange) {
				 // Check if field is explicitly disabled via filter
				 if (fieldSettings && 
					 fieldSettings[fieldName] && 
					 fieldSettings[fieldName].hasOwnProperty('enabled') && 
					 fieldSettings[fieldName].enabled === false) {
					 return null;
				 }
				 
				 var fieldLabel = (fieldSettings && fieldSettings[fieldName] && fieldSettings[fieldName].label) || label;
				 var fieldPlaceholder = (fieldSettings && fieldSettings[fieldName] && fieldSettings[fieldName].placeholder) || '';
				 
				 return createElement(TextControl, {
					 label: fieldLabel,
					 type: fieldType || 'text',
					 value: value || '',
					 placeholder: fieldPlaceholder,
					 onChange: onChange
				 });
			 }

			 // Helper function to render taxonomy select
			 function renderTaxonomySelect(taxonomyName, label, value, onChange, options) {
				 if (!taxonomySettings[taxonomyName] || !taxonomySettings[taxonomyName].enabled) {
					 return null;
				 }
				 
				 var taxLabel = (taxonomySettings[taxonomyName] && taxonomySettings[taxonomyName].label) || label;
				 
				 // Ensure value is a string to prevent React warnings
				 var safeValue = value ? String(value) : '';
				 
				 // Get options with error handling
				 var selectOptions;
				 try {
					 selectOptions = options();
				 } catch (error) {
					 console.error('Error getting taxonomy options for ' + taxonomyName + ':', error);
					 selectOptions = [{ label: 'Error loading options', value: '', disabled: true }];
				 }
				 
				 return createElement(SelectControl, {
					 label: taxLabel,
					 value: safeValue,
					 options: selectOptions,
					 onChange: function(newValue) {
						 // Ensure we're passing a string value
						 onChange(newValue || '');
					 }
				 });
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
										   style: { maxWidth: '500px', height: 'auto' }
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
 
					 // Fields Section - Only render enabled fields
					 createElement(
						 'div',
						 { className: 'team-member-fields' },
						 renderField('position', 'text', 'Position', attributes.position, updatePosition),
						 renderField('qualification', 'text', 'Qualification', attributes.qualification, updateQualification),
						 renderField('phone', 'text', 'Phone', attributes.phone, updatePhone),
						 renderField('email', 'email', 'Email', attributes.email, updateEmail),
						 renderTaxonomySelect('location', 'Location', attributes.selectedLocation, updateLocation, getLocationOptions),
						 renderTaxonomySelect('role', 'Job Role', attributes.selectedRole, updateRole, getRoleOptions)
					 )
				 ),
				 createElement(
					 InspectorControls,
					 null,
					 createElement(
						 PanelBody,
						 { title: 'Team Member Details' },
						 renderField('position', 'text', 'Position', attributes.position, updatePosition),
						 renderField('qualification', 'text', 'Qualification', attributes.qualification, updateQualification),
						 renderField('phone', 'text', 'Phone', attributes.phone, updatePhone),
						 renderField('email', 'email', 'Email', attributes.email, updateEmail),
						 renderTaxonomySelect('location', 'Location', attributes.selectedLocation, updateLocation, getLocationOptions),
						 renderTaxonomySelect('role', 'Job Role', attributes.selectedRole, updateRole, getRoleOptions)
					 )
				 )
			 );
		 },
 
		 save: function () {
			 return null;
		 }
	 });
 })(window.wp);