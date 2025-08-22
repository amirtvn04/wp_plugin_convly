# Convly Plugin File Structure

```
convly/
├── convly.php                          # Main plugin file
├── README.md                           # Plugin documentation
├── languages/                          # Translation files directory
│   └── convly.pot                     # Translation template (to be created)
│
├── includes/
│   ├── class-convly-activator.php     # Handles plugin activation
│   ├── class-convly-deactivator.php   # Handles plugin deactivation
│   ├── class-convly-core.php          # Core plugin class
│   ├── class-convly-loader.php        # Registers hooks and filters
│   ├── class-convly-i18n.php          # Internationalization
│   ├── class-convly-ajax.php          # AJAX request handlers
│   ├── class-convly-tracker.php       # Tracking functionality (optional)
│   ├── class-convly-reports.php       # Report generation (optional)
│   └── class-convly-pdf-generator.php # PDF report generator
│
├── admin/
│   ├── css/
│   │   └── convly-admin.css           # Admin styles
│   ├── js/
│   │   └── convly-admin.js            # Admin JavaScript
│   ├── partials/
│   │   ├── convly-admin-dashboard.php # Dashboard view
│   │   ├── convly-page-details.php    # Page details view
│   │   └── convly-settings.php        # Settings page view
│   └── class-convly-admin.php         # Admin functionality
│
├── public/
│   ├── css/
│   │   └── convly-public.css          # Public styles (if needed)
│   ├── js/
│   │   └── convly-public.js           # Public tracking JavaScript
│   └── class-convly-public.php        # Public functionality
│
└── assets/                             # Additional assets (optional)
    ├── images/
    └── fonts/
```

## Setup Instructions

1. **Create the directory structure**:
   ```bash
   mkdir -p convly/{includes,admin/{css,js,partials},public/{css,js},languages,assets/{images,fonts}}
   ```

2. **Add the files to their respective locations** as shown in the structure above.

3. **Important Notes**:
   - The main file `convly.php` should be in the root directory
   - All class files should follow WordPress naming conventions
   - The Ajax handler methods from `convly-page-stats-ajax.php` should be added to the `class-convly-ajax.php` file
   - Make sure file permissions are set correctly (usually 644 for files, 755 for directories)

4. **Additional files you might want to create**:
   - `uninstall.php` - For cleanup when plugin is deleted
   - `LICENSE.txt` - GPL v2 license text
   - `.gitignore` - If using version control
   - `composer.json` - If using Composer for dependencies

5. **Before activation**:
   - Ensure all files are properly uploaded
   - Check that your WordPress installation meets the minimum requirements
   - Make a backup of your database before activating

## Testing the Plugin

1. Upload the entire `convly` folder to `/wp-content/plugins/`
2. Activate the plugin from WordPress admin
3. Check that database tables are created successfully
4. Test tracking on a few pages
5. Verify that the dashboard displays data correctly

## Notes for Future Development

- Consider adding support for WooCommerce conversion tracking
- Add A/B testing capabilities
- Implement heat map functionality
- Add email notifications for conversion rate changes
- Create REST API endpoints for external integrations