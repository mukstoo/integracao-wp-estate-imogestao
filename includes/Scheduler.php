<?php
require_once plugin_dir_path(__DIR__) . 'includes/ApiHandler.php';
class Scheduler
{
    public function __construct()
    {
        add_action('wp', array($this, 'schedule_data_sync'));
        add_action('sync_data_cron_job', array($this, 'run_sync_data_cron_job'));
    }

    public function schedule_data_sync()
    {
        if (!wp_next_scheduled('sync_data_cron_job')) {
            wp_schedule_event(time(), 'hourly', 'sync_data_cron_job'); // You can adjust the frequency
        }
    }

    public function run_sync_data_cron_job()
    {
        $apiHandler = new ApiHandler();
        $apiHandler->fetch_data();
    }
}