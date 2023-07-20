<?php
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
        add_action('wp_ajax_set_api_key', array($this, 'set_api_key')); // Add this line
    }

    /**
     * Add settings pages.
     */
    public function add_pages()
    {
        add_menu_page(__('Integracao Imogestao', 'myplugin'), __('Integracao Imogestao', 'myplugin'), 'manage_options', 'imogestao', array($this, 'settings_page'), '', 2);
    }

    /**
     * Display the settings page.
     */
    public function settings_page()
    {
        include plugin_dir_path(__DIR__) . 'admin/settings-page.php';
    }

    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts($hook)
    {
        // Only enqueue the script and style on the settings page
        if ($hook != 'toplevel_page_imogestao') {
            return;
        }

        wp_enqueue_script('myplugin-script', plugins_url('admin/js/myplugin.js', __DIR__), array('jquery'), '1.0', true);
        wp_enqueue_style('myplugin-style', plugins_url('admin/css/myplugin.css', __DIR__), array(), '1.0');
        wp_localize_script('myplugin-script', 'myplugin_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }


    // Add this method
    public function set_api_key()
    {
        $api_key = $_GET['api_key'];
        update_option('my_api_key', $api_key);
        wp_send_json_success(['message' => 'API key set successfully.']);
    }
}