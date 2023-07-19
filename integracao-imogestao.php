<?php

/**
 * Plugin Name: Best Integracao Imogestao
 * Description: A plugin to fetch data from an external API and populate the WP database.
 * Version: 1.0
 * Author: Your Name
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the Admin class
require_once plugin_dir_path(__FILE__) . 'admin/AdminSettings.php';

// Include the classes in the includes folder
require_once plugin_dir_path(__FILE__) . 'includes/ApiHandler.php';
require_once plugin_dir_path(__FILE__) . 'includes/PostManager.php';
require_once plugin_dir_path(__FILE__) . 'includes/ImageManager.php';

class MyPlugin
{
    private $admin;

    public function __construct()
    {
        $this->admin = new AdminSettings();
    }

    public function run()
    {
        $this->admin->init();
    }
}

$myPlugin = new MyPlugin();
$myPlugin->run();

function my_admin_notice()
{
    global $pagenow;
    if ($pagenow == 'myplugin/myplugin-admin-page.php') {
        $message = apply_filters('my_plugin_message', '');
        if (!empty($message)) {
?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e($message, 'my-text-domain'); ?></p>
            </div>
<?php
        }
    }
}
add_action('admin_notices', 'my_admin_notice');

function set_my_plugin_message($message)
{
    add_filter('my_plugin_message', function () use ($message) {
        return $message;
    });
}
