=== Restaurant Table Booking ===
Contributors: yourname
Tags: restaurant, booking, reservation, table, food, multisite
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network: true

A comprehensive restaurant table booking system with admin panel, email notifications, responsive frontend, and full WordPress Multisite support.

== Description ==

Restaurant Table Booking is a powerful WordPress plugin that allows restaurants to manage table reservations directly from their website. The plugin features a beautiful, responsive booking form and a comprehensive admin panel for managing bookings, business hours, locations, and notifications.

**✅ FULL MULTISITE SUPPORT** - This plugin is fully compatible with WordPress Multisite networks, with independent booking systems for each site.

= Features =

* **Responsive Booking Form** - Beautiful, mobile-friendly reservation form
* **Multiple Dining Locations** - Support for different dining areas with images
* **Business Hours Management** - Set operating hours for each day of the week
* **Time Slot Management** - Configurable time intervals (15, 30, or 60 minutes)
* **Email Notifications** - Automatic confirmation emails to customers and staff
* **Admin Dashboard** - Complete booking management interface
* **Conflict Prevention** - Prevents double bookings and past-date reservations
* **Customizable Settings** - Flexible configuration options
* **Shortcode Support** - Easy integration with any page or post
* **24/12 Hour Time Format** - Choose your preferred time display format
* **Overnight Hours Support** - Handle restaurants open past midnight
* **WordPress Multisite Compatible** - Full support for network installations

= Multisite Features =

* **Independent Systems** - Each site has its own booking data, settings, and locations
* **Network Admin Dashboard** - Overview of all sites and their booking statistics
* **Automatic Setup** - New sites automatically get plugin tables created
* **Automatic Cleanup** - Site deletion removes all associated booking data
* **Site-Specific Configuration** - Each site can have different business hours, locations, and settings
* **Network Activation** - Can be activated network-wide or per-site

= Shortcode Usage =

Use `[restaurant_booking_form]` to display the booking form on any page or post.

= Admin Features =

* View all bookings with filtering options
* Manage business hours for each day (including overnight hours)
* Add/edit/delete dining locations with images
* Configure email notifications
* Set time intervals and booking limits
* Choose 24-hour or 12-hour time format
* Network admin overview (multisite only)

== Installation ==

= Single Site Installation =

1. Upload the plugin files to the `/wp-content/plugins/restaurant-table-booking` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Restaurant Bookings menu item to configure the plugin settings
4. Add the `[restaurant_booking_form]` shortcode to any page where you want the booking form to appear

= Multisite Installation =

1. Upload the plugin files to the `/wp-content/plugins/restaurant-table-booking` directory
2. **Network Activate** the plugin through the 'Plugins' screen in Network Admin
3. Each site will automatically get its own booking system with default settings
4. Site administrators can configure their individual settings via Restaurant Bookings menu
5. Network administrators can view overview statistics in Network Admin > Restaurant Bookings

== Frequently Asked Questions ==

= Does this plugin support WordPress Multisite? =

**Yes!** This plugin has full WordPress Multisite support. Each site in your network gets its own independent booking system with separate settings, locations, and booking data.

= How do I display the booking form? =

Use the shortcode `[restaurant_booking_form]` on any page or post where you want the booking form to appear.

= Can I customize the business hours? =

Yes, go to Restaurant Bookings > Settings > Business Hours to set your operating hours for each day of the week. The plugin supports overnight hours (e.g., 14:00 - 01:00).

= How do I add dining locations? =

Navigate to Restaurant Bookings > Settings > Locations to add, edit, or remove dining locations with custom images.

= Can customers receive confirmation emails? =

Yes, the plugin automatically sends confirmation emails to customers and can notify multiple staff email addresses.

= Is the booking form mobile-friendly? =

Absolutely! The booking form is fully responsive and works perfectly on all devices.

= What happens when I create a new site in my multisite network? =

The plugin automatically creates the necessary database tables and default settings for the new site.

= What happens when I delete a site from my multisite network? =

All booking data, settings, and locations for that site are automatically removed to keep your database clean.

= Can each site have different settings in multisite? =

Yes! Each site has completely independent settings, business hours, locations, and booking data.

== Screenshots ==

1. Booking form frontend
2. Admin bookings dashboard
3. Business hours settings with overnight support
4. Location management with images
5. Email notification settings
6. Network admin overview (multisite)
7. Time format selection (24h/12h)

== Changelog ==

= 1.0.0 =
* Initial release
* Responsive booking form
* Admin dashboard
* Business hours management (including overnight hours)
* Location management with images
* Email notifications
* Shortcode support
* 24/12 hour time format selection
* **Full WordPress Multisite support**
* Network admin dashboard
* Automatic site setup and cleanup
* Independent booking systems per site

== Upgrade Notice ==

= 1.0.0 =
Initial release of Restaurant Table Booking plugin with full WordPress Multisite support.

== Multisite Documentation ==

= Network Activation =

When you network activate this plugin, it automatically:
- Creates database tables for all existing sites
- Sets up default settings and locations for each site
- Provides a network admin dashboard for overview

= Per-Site Management =

Each site administrator can:
- Manage their own bookings and settings
- Configure business hours and locations
- Set up email notifications
- Customize time formats and intervals

= Network Administration =

Network administrators can:
- View statistics across all sites
- See which sites are actively using the plugin
- Access individual site admin panels
- Monitor total bookings across the network

= Data Independence =

Important: Each site's data is completely independent:
- Bookings are not shared between sites
- Settings are site-specific
- Email notifications use each site's admin email
- Business hours can be different for each location