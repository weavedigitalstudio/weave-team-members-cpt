# Weave Team Member CPT Module Theme Integration Guide

## Installation Steps

1. Add this code below to the theme's `functions.php` file:

```php
/**
 * Include Team Members CPT customisations
 */
require_once get_stylesheet_directory() . '/inc/post-types/team-cpt-setup.php';
```

2. Add `team-cpt-setup.php` to `/inc/post-types/` directory in your theme

3. Adjust `team-cpt-setup.php` as needed for your specific requirements

4. Add the plugin folder to `/inc/post-types/` directory

## Notes

- Make sure the `/inc/post-types/` directory exists in your theme before adding files
- The integration enables custom post type functionality for team members
- Customize the display and fields in the `team-cpt-setup.php` file
