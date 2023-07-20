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

/* class MyPlugin
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
$myPlugin->run(); */


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
        add_action('init', array($this, 'register_litoral_taxonomy'));
    }

    public function register_litoral_taxonomy()
    {
        register_taxonomy(
            'litoral',
            'estate_property',
            array(
                'label' => __('Litoral'),
                'rewrite' => array('slug' => 'litoral'),
                'hierarchical' => false,
            )
        );
    }
}

$myPlugin = new MyPlugin();
$myPlugin->run();
