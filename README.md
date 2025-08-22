# Convly - WordPress Conversion Rate Tracker

Track conversion rates for each page of your WordPress website by monitoring button clicks and page views.

## Description

Convly is a comprehensive WordPress plugin that helps you track and analyze the conversion rates of your website pages. It monitors page views and button/link clicks to calculate conversion rates, providing valuable insights into your content performance.

### Key Features

- **Page View Tracking**: Automatically tracks all page views with unique visitor identification
- **Button/Link Click Tracking**: Monitor specific buttons and links on each page
- **Conversion Rate Calculation**: Real-time conversion rate calculation for each page
- **Device Breakdown**: See the percentage of mobile vs desktop visitors
- **Advanced Filtering**: Filter data by date ranges and sort by various metrics
- **Custom Tabs**: Organize pages into custom categories beyond the default Pages/Products/Posts
- **Visual Charts**: Beautiful charts showing trends over time
- **PDF Reports**: Export detailed reports for specific date ranges
- **Cache Compatible**: Works seamlessly with popular caching plugins
- **AJAX-Powered**: Smooth, no-refresh interface for better user experience

## Installation

1. Upload the `convly` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the Convly menu in your WordPress admin panel
4. Start adding buttons to track on your pages

## Usage

### Getting Started

1. After activation, go to **Convly** in your WordPress admin menu
2. The dashboard will show three summary cards:
   - Total Conversion Rate
   - Total Clicks  
   - Total Views

### Adding Buttons to Track

1. Navigate to the pages list in the Convly dashboard
2. Click "Add Button" next to any page
3. Enter the CSS ID of the button you want to track (without the # symbol)
4. Give it a friendly name for easy identification
5. Choose whether it's a button or link
6. Save the configuration

### Viewing Page Details

1. Click "Details" on any page in the list
2. View detailed statistics including:
   - Page views over time
   - Device breakdown (mobile/desktop)
   - Individual button performance
   - Conversion trends

### Creating Custom Tabs

1. Click the "+" button next to the default tabs
2. Enter a name for your custom tab
3. Pages can be assigned to custom tabs for better organization

### Generating Reports

1. Click "Export PDF Report" on the dashboard
2. Select your desired date range
3. The report will include all active pages and their performance metrics

## Configuration

Navigate to **Convly > Settings** to configure:

- **Enable Tracking**: Toggle tracking on/off
- **Track Logged-in Users**: Choose whether to track logged-in users
- **Excluded User Roles**: Select which user roles to exclude from tracking
- **Cache Compatibility**: Enable for better compatibility with caching plugins

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher
- JavaScript enabled in the browser

## Database Tables

The plugin creates the following tables:

- `wp_convly_views` - Stores page view data
- `wp_convly_clicks` - Stores button click data
- `wp_convly_buttons` - Stores button configurations
- `wp_convly_pages` - Stores page settings
- `wp_convly_tabs` - Stores custom tab configurations

## Privacy & GDPR

- Convly uses cookies to identify unique visitors
- No personally identifiable information is collected
- All data is stored locally in your WordPress database
- No data is sent to external services

## Troubleshooting

### Tracking not working

1. Check if tracking is enabled in settings
2. Ensure JavaScript is enabled in your browser
3. Check if your user role is excluded from tracking
4. Clear your cache if using a caching plugin

### Buttons not being tracked

1. Verify the CSS ID is correct (view page source to confirm)
2. Ensure the button exists on the page
3. Check if the page is active in Convly
4. Try using the browser's developer console to check for JavaScript errors

## Changelog

### Version 1.0.0
- Initial release
- Page view tracking
- Button click tracking
- Conversion rate calculation
- Dashboard with charts
- PDF report generation
- Custom tabs support
- Mobile/Desktop breakdown

## Support

For support, feature requests, or bug reports, please visit our support forum or create an issue on our GitHub repository.

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```