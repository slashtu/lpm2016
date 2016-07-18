=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (https://www.role-editor.com)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 4.0
Tested up to: 4.5.2
Stable tag: 4.25.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With User Role Editor WordPress plugin you may change WordPress user roles and capabilities easy.

== Description ==

With User Role Editor WordPress plugin you can change user role capabilities easy.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" and adjust plugin options according to your needs. For WordPress multisite URE options page is located under Network Admin Settings menu.
5. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

In case you have a free version of User Role Editor installed: 
Pro version includes its own copy of a free version (or the core of a User Role Editor). So you should deactivate free version and can remove it before installing of a Pro version. 
The only thing that you should remember is that both versions (free and Pro) use the same place to store their settings data. 
So if you delete free version via WordPress Plugins Delete link, plugin will delete automatically its settings data. 
You will have to configure User Role Editor Pro Settings again after that.
Right decision in this case is to delete free version folder (user-role-editor) via FTP, not via WordPress.


== Changelog ==
= [4.25.1] 17.05.2016 =
* Core version: 4.25.3
* Fix: Content View Restrictions module: 
    - Compatibility provided with WordPress versions earlier 4.4, which do not send 'post' parameter to "get_{$adjacent}_post_where" filter.
    - Conflict was resolved with WPML plugin. It adds 'p' parameter to the queries for a single post.Titles of restricted posts were viewable for that reason.
* Fix: Admin menu access module:
    - If the 1st submenu item was blocked, menu item was renamed and lost its submenu with not blocked menu items.
* New: Content Edit Restrictions module: filter 'ure_restrict_edit_post_type' was added. It allows to exclude some post type (you don't wish to restrict) from this module action.

= [4.25] 05.05.2016 =
* Core version: 4.25.2
* New: Edit posts restrictions module: 
    - It's possible to set edit posts/pages/custom post types restrictions for roles.
    - Option 'Own data only' was added to allow to edit/see at admin just own posts/pages, custom post type items.
    - Support was added for "Woocommerce Bookings" plugin.
* Fix: Edit posts restrictions module: 
    - It was not possible to use revisions with 'Allow' model.
    - edit restrictions were not applied to a user without 'edit_posts' or 'edit_pages' capability.
    - WooCommerce orders are filtered now with taking in account product owner ID, if you restricted a user by authors(product owners) ID. 
      It's possible to switch off this extention via filter 'ure_edit_posts_access_add_orders_by_product_owner'. It should return false for that.
    - Quant by views was shown wrong for some custom post types, e.g. WooCommerce Orders.
    - Bulk update from posts list wrote to the user profile wrong data.
* Fix: Admin menu access module:
    - 'user-edit.php' link was blocked by error with 'block not selected' model, which did not allow to edit a selected user.        
    - access was allowed by error via direct URL to some menu items blocked with "block not selected" model.
    - Jetpack menu was not blocked. Admin menu copy creation is linked to the action with priority 999, to be executed after Jetpack, 
      which uses priority 998 for some reason.
    - 'UpdraftPlus' topbar admin menu was not removed when 'Settings->UpdraftPlus Backup' menu item was blocked.
* Fix: Content view restrictions module:
    - Prohibited posts titles/URLs were shown as 'Previous' or 'Next' links at the single post page.
* Update: Edit posts restrictions module: bulk update is available for all custom post types, not for the posts and pages only as it was earlier. 
* Update: Admin menu access module:
    - Enhanced technique of blocking links: order and quant of URL parameters does not matter.
    - Internal admin menu copy is refreshed automatically after any plugin activation for synchronization with possible menu changes.
    - When menu item is not allowed, it's replaced by the 1st allowed item from a child submenu or removed.
    - Multisite "My Sites" top bar admin menu does not show 'Dashboard' menu item for site if it's blocked for that site. 
      'New Post', 'Manage Comments' menu items are shown for user who can edit posts.
    - Some plugins/themes produces Menu/Submenu glitches ((Ultimate, Avada) for users with changed permissions. Such menu inconsistencies are fixed automatically.
* Update: Enhanced inner processing of available custom post types list.
* Update: Uses 15 seconds transient cache in order to not count users without role twice when 'restrict_manage_users' action fires.
* Update: URE fires action 'profile_update' after direct update of user permissions in order other plugins may catch such change.
* Update: All URE's PHP classes files were renamed and moved to the includes/classes subdirectory. Pro version part was moved under the "pro" directory.

= [4.24.6] 15.04.2016 =
* Core version: 4.25.1
* Fix: Selected role's capabilities list was returned back to old after click "Update" button. It was showed correctly according to the recent updates just after additional page refresh.
* Update: deprecated function get_current_user_info() call was replaced with wp_get_current_user().

= [4.24.5] 02.04.2016 =
* Core version: 4.25
* Important security update: Any registered user could get an administrator access. Thanks to [John Muncaster](http://johnmuncaster.com/) for discovering and wisely reporting it.
* URE pages title tag was replaced from h2 to h1, for compatibility with other WordPress pages.

= [4.24.4] 01.04.2016 =
* Core version: 4.24.1
* Fix: Content view restrictions module: Access error message was not shown with setting to show it. Post or page was excluded from the list of available content instead.
* Fix: Admin menu access module:
    - 'user-edit.php' link was blocked by error with 'block not selected' model, which did not allow to edit a selected user.
    - admin menu copy is linked to the action with priority 1000, to be executed after Jetpack, which uses priority 998 for some reason.

= [4.24.3] 23.03.2016 =
* Core version: 4.24.1
* Fix: PHP Notice:  Undefined index: ... in wp-content\plugins\user-role-editor-pro\includes\pro\classes\admin-menu-access.php on line 69
       Warning: Invalid argument supplied for foreach() in wp-content/plugins/user-role-editor-pro/includes/pro/classes/admin-menu-access.php on line 86
* Update: Admin menu access module - conditions were optimized when backend admin menu copy is created.

= [4.24.2] 21.03.2016 =
* Core version: 4.24.1
* Fix: Critical bugs at URE_Content_View_Restrictions_Posts_List class.
* Update: Admin menu access module: It  hides 'Dashboard' menu item from admin top bar menu in case this item blocked for the main (left side) admin menu.

= [4.24.1] 19.03.2016 =
* Core version: 4.24.1
* Fix: Fatal error: Undefined class constant ‘content_for_roles’ in wp-content/plugins/user-role-editor-pro/includes/pro/classes/content-view-restrictions-posts-list.php on line 46

= [4.24] 19.03.2016 =
* Core version: 4.24.1
* Fix: Error message "Update Failed: Plugin update failed" was shown after click "Update" at WordPress multisite "Network Admin - Plugins" page.
* Fix: PHP Fatal error: Call to undefined method URE_Posts_Edit_Access::get_attachments_list() in wp-content/plugins/user-role-editor-pro/includes/pro/classes/posts-edit-access.php on line 171
* Fix: Admin menu access module: 
    - "Block not Selected" model blocked allowed URLs inside Media Library (started from upload.php) and Appearance Menus (started from nav-menus.php) and other allowed URLs (just with additional parameters).
    - WordPress Multisite - single site administrator get "Admin menu" dialog in the read-only mode for all roles. He should not edit just its own 'administrator' role.
    - Direct access to the blocked menu item via URL was possible for URLs like one from WooCommerce's "Products->Attributes" menu item: 
           wp-admin/edit.php?post_type=product&page=product_attributes
    - Blocked menu item could be selected as the 1st available menu item which leaded to the endless redirect loop.
    - Blocked menu items search at the current user submenu copy was optimized (expanded or reordered submenu cases (like Events Manager) are processed correctly now).
* Fix: Meta Boxes access module: WooCommerce edit product/order page meta boxes were not hidden, fixed PHP notices generated in some cases.
* Fix: Export/Import module: PHP Notice: "Use of undefined constant value" was shown during import.
* Update: Export/Import module: applies base64 encode/decode to the processed data in order to exclude errors when working with multi-byte languages, like Japanese.
  Refresh your exported roles files as older data format is not supported starting from this version.
* Update: Admin menu access module - full copy of admin menu is created only when superadmin opens User Role Editor.
* Update: Meta Boxes access module: 
    - meta boxes are grouped by page to which they belong and sorted in alphabet order. 
    - meta boxes created by Advanced Custom Fields plugin are available at URE and can be blocked now.
* New: Export/Import module:'ure_sanitize_capability_filter' filter was added. Use it to redefine user capability name valid characters set.Currently only letters, numbers, spaces, '_','-', '/' are allowed.
* New: User capabilities page was integrated with "[User Switching](https://wordpress.org/plugins/user-switching/)" plugin - "Switch To" the editing user link iss added if "User Switching" plugin is available.
* Fix: PHP notice was generated by class-role-additional-options.php in case when some option does not exist anymore
* Update: 'Add Capability' button have added capability to the WordPress built-in administrator role by default. It did not work, if 'administrator' role did not exist.
  Now script selects automatically as an admin role a role with the largest quant of capabilities and adds new capability to the selected role.
* Fix: "Assign role to the users without role" feature ignored role selected by user.
* Marked as compatible with WordPress 4.5.

= [4.23.2] 14.02.2016 =
* Core version: 4.23.3
* Fix: Admin menu access add-on: 
    1) It was not possible to block top level menu items when menu was reordered, by some plugin, like WooCommerce.
    2) Support for virtual 'exist' user capability was added. WordPress adds it automatically to every user.
       It was not possible to block 'Visual Composer' top level menu and its 'About' submenu item. These points are protected by 'exist' capability.

= [4.23.1] 10.02.2016 =
* Core version: 4.23.3
* Fix: Admin menu access add-on: Direct URL was not blocked for the blocked admin menu item with link started from 'admin.php'.

= [4.23] 07.02.2016 =
* Core version: 4.23.3
* Update: Call of deprecated mysql_server_info() is replaced with $wpdb->db_version().
* Update: Singleton pattern is applied to the URE_Lib class.
* Update: Code executed once after plugin activation is executed by the next request to WP and may use a WordPress action to fire with a needed priority.
* Update: Unused 'add_users' capability was removed from the list of core capabilities as it was removed from WordPress starting from version 4.4  
* Fix: "Users - Without Role" button showed empty roles drop down list on the 1st call. 
* Fix: ure-users.js was loaded not only to the 'Users' page.
* Fix: PHP notice was generated by class-role-additional-options.php in case when some option does not exist anymore
* New: Full support for bbPress user capabilities and roles was added.
* New: Edit posts restrictions add-on: Rules are applied automatically to the child pages of the allowed/blocked page.
  Use 'ure_auto_access_child_pages' filter if you wish to stop automatic inclusion of the child pages to the edit restriction rules.
  It should be used as a 'must-use' plugin, because of this filter is applied earlier than a theme is loaded.
  It seems that it's too late to insert it into the theme's functions.php file.
* Update: Content view restrictions are applied now to the front-end only. Use edit restrictions add-on to manage posts/pages visibility at WordPress back-end.
* Update: data update notice is shown now for all add-ons.
* Update: get_all_category_ids() function call deprecated from WordPress version 4.0 is replaced by call of get_terms() function.
* Fix: Edit posts restrictions add-on:
    1) Blocked categories are not available now for selection at the new created post and at the posts list filter by category drop-down list.
    2) Earlier you can not save new post with category assigned from the allowed categories list with error: You are not allowed to edit this post. 
       New post is created now with 1st category (from the allowed list) automatically assigned.
    3) Data (saved at user level) have been deleted in case user attributes was updated not from WordPress back-end user profile page, but by directly, via WordPress API.
    4) Posts filters and counters were enhanced for the case when user does not have posts available for editing.    
* Fix: Admin menu access add-on: in some cases click on allowed(shown) menu item showed "Not enough permissions" error message.
  Important: make database backup before installing this version. Admin menu access data is converted after plugin activation to the new format.
  Data conversion from version older than 4.15 is not supported. If you have URE Pro version older than 4.15 install and activate URE version 4.21.1 first,
  then proceed with version 4.22 and later.
* Fix: 'ure_restrict_content_view_for_authors_and_editors' filter does blocked saving content view restrictions data at a post level.
* Fix: Network Admin - Users - Capabilities - 'Update Network' button did not work.
* Fix: 'Update Network' did not replicate 'Widgets' access add-on data if selected.
* Fix: PHP notices were shown in some cases.
* Update: some HTML-code extracted from User_Role_Editor_Pro class to URE_Pro_View class.
* Update: It's possible now to manage access to 'Metaboxes' for roles without 'edit_posts' capability.

Click [here](http://role-editor.com/changelog)</a> to look at [the full list of changes](http://role-editor.com/changelog) of User Role Editor plugin.
