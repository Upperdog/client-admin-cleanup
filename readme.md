# Client Admin Cleanup

This WordPress plugin hides and restricts access to some parts of the wp-admin for clients. For example, it hides and restricts access to the plugins, themes, and updates sections. Developers and agencies who maintain their client's sites usually want to have full control of these parts of the site.

___This plugin is not released in the official WordPress Plugins directory. It is developed for our own needs. We will not be able provide support for this plugin. Please fork the plugin if you want to customize it to your own needs.___

This plugin: 

* Restricts access to plugins, themes, customizer, updates, and Jetpack. 
* Removes unused dashboard widgets.

## Specify allowed users

Developers can use the `clients_admin_cleanup_allowed_users` filter to specify which users are allowed to see update notifications. Add the username of each allowed user to an array like in the following example: 

```
function site_client_admin_cleanup_allowed_users() {
    $allowed_users = array( 'bill', 'melinda' );
    return $allowed_users;
}
add_filter( 'client_admin_cleanup_allowed_users', 'site_client_admin_cleanup_allowed_users' );
```

## Compatibility with remote management services

This plugin has only been tested with ManageWP. ManageWP has to connect to the site with a user account that is allowed to see updates. 

## Manual installation

1. Upload the `client-admin-cleanup` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Composer installation

Run the following command to add the plugin to your composer.json file. 

```
composer require upperdog/client-admin-cleanup
```

## Changelog

### 1.0.0 (2018-10-27)

* Initial release.