<?php

namespace BestIntegracaoImogestao\Admin;

use BestIntegracaoImogestao\Includes\ApiHandler;
use BestIntegracaoImogestao\Includes\Scheduler;

class AdminSettings
{
    private ApiHandler $apiHandler;
    private Scheduler $scheduler;

    public function __construct()
    {
        $this->apiHandler = new ApiHandler();
    }

    public function init(): void
    {
        $this->scheduler = new Scheduler();  // Initialize Scheduler here
        $this->scheduler->setup_schedule();  // Initialize the schedule

        add_action('admin_menu', [$this, 'addPages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_fetch_data', [$this->apiHandler, 'fetchData']);
        add_action('wp_ajax_clear_data', [$this->apiHandler, 'clearData']);
        add_action('wp_ajax_set_api_key', [$this, 'setApiKey']);
        add_action('wp_ajax_set_next_run_time', [$this, 'setNextRunTime']);
        add_action('wp_ajax_get_last_run_log', [$this, 'getLastRunLog']);
        add_action('wp_ajax_get_next_run_time', [$this, 'getNextRunTime']);
    }

    public function addPages(): void
    {
        add_menu_page(
            __('Integracao Imogestao', 'myplugin'),
            __('Integracao Imogestao', 'myplugin'),
            'manage_options',
            'imogestao',
            [$this, 'settingsPage'],
            '',
            2
        );
    }

    public function settingsPage(): void
    {
        include plugin_dir_path(__DIR__) . 'admin/settings-page.php';
    }

    public function enqueueScripts(string $hook): void
    {
        if ($hook !== 'toplevel_page_imogestao') {
            return;
        }

        wp_enqueue_script(
            'myplugin-script',
            plugins_url('admin/js/myplugin.js', __DIR__),
            ['jquery'],
            '1.0',
            true
        );

        wp_enqueue_style(
            'myplugin-style',
            plugins_url('admin/css/myplugin.css', __DIR__),
            [],
            '1.0'
        );

        wp_localize_script(
            'myplugin-script',
            'myplugin_ajax',
            ['ajax_url' => admin_url('admin-ajax.php')]
        );
    }

    public function setApiKey(): void
    {
        $apiKey = sanitize_text_field($_GET['api_key'] ?? '');
        update_option('my_api_key', $apiKey);
        wp_send_json_success(['message' => 'API key set successfully.']);
    }

    public function getNextRunTime(): void
    {
        $nextRunTime = Scheduler::get_next_run_time();
        wp_send_json_success(['next_run_time' => $nextRunTime]);
    }

    public function setNextRunTime(): void
    {
        $nextRunTime = sanitize_text_field($_GET['next_run_time'] ?? '');
        Scheduler::update_schedule_time($nextRunTime);
        wp_send_json_success(['message' => 'Next run time set successfully.', 'next_run_time' => $nextRunTime]);
    }


    public function getLastRunLog(): void
    {
        $log = Scheduler::get_log();
        $logArray = explode(", ", $log);  // Convert the log string to an array
        wp_send_json_success(['log' => $logArray]);
    }
}
