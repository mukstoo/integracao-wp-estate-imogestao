<?php
/* 
require_once plugin_dir_path(__DIR__) . 'includes/ApiHandler.php';

class AdminSettings
{
    private $apiHandler;

    public function __construct()
    {
        $this->apiHandler = new ApiHandler(); // Create an instance of the ApiHandler class
    }


    // Initialize the admin settings.

    public function init()
    {
        add_action('admin_menu', array($this, 'add_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_fetch_data', array($this->apiHandler, 'fetch_data')); // Use the ApiHandler instance
        add_action('wp_ajax_clear_data', array($this->apiHandler, 'clear_data')); // Use the ApiHandler instance
    }


    // Add settings pages.

    public function add_pages()
    {
        add_options_page(__('Test Settings', 'myplugin'), __('Test Settings', 'myplugin'), 'manage_options', 'testsettings', array($this, 'settings_page'));
    }


    // Display the settings page.

    public function settings_page()
    {
        include plugin_dir_path(__DIR__) . 'admin/settings-page.php';
    }



    // Enqueue scripts.

    public function enqueue_scripts($hook)
    {
        // Only enqueue the script on the settings page
        if ($hook != 'settings_page_testsettings') {
            return;
        }

        wp_enqueue_script('myplugin-script', plugins_url('admin/js/myplugin.js', __DIR__), array('jquery'), '1.0', true);
        wp_localize_script('myplugin-script', 'myplugin_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }
} */

require_once plugin_dir_path(__DIR__) . 'includes/ApiHandler.php';

class AdminSettings
{
    private $apiHandler;

    public function __construct()
    {
        $this->apiHandler = new ApiHandler(); // Create an instance of the ApiHandler class
    }

    /**
     * Initialize the admin settings.
     */
    public function init()
    {
        add_action('admin_menu', array($this, 'add_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_fetch_data', array($this->apiHandler, 'fetch_data')); // Use the ApiHandler instance
        add_action('wp_ajax_clear_data', array($this->apiHandler, 'clear_data')); // Use the ApiHandler instance
    }

    /**
     * Add settings pages.
     */
    public function add_pages()
    {
        add_options_page(__('Test Settings', 'myplugin'), __('Test Settings', 'myplugin'), 'manage_options', 'testsettings', array($this, 'settings_page'));
    }

    /**
     * Display the settings page.
     */
    public function settings_page()
    {
        include plugin_dir_path(__DIR__) . 'admin/settings-page.php';
    }


    /**
     * Enqueue scripts.
     */
    public function enqueue_scripts($hook)
    {
        // Only enqueue the script on the settings page
        if ($hook != 'settings_page_testsettings') {
            return;
        }

        wp_enqueue_script('myplugin-script', plugins_url('admin/js/myplugin.js', __DIR__), array('jquery'), '1.0', true);
        wp_localize_script('myplugin-script', 'myplugin_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }
}
