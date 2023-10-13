<?php

namespace BestIntegracaoImogestao\Includes;

class Scheduler
{
    const SCHEDULE_HOOK = 'best_integracao_imogestao_daily_task';
    const LOG_OPTION_KEY = 'best_integracao_imogestao_daily_log';
    const SCHEDULE_TIME_OPTION_KEY = 'best_integracao_imogestao_schedule_time';

    public function __construct()
    {
        $this->setup_schedule();
    }

    public function setup_schedule()
    {
        $scheduledTime = get_option(self::SCHEDULE_TIME_OPTION_KEY, '05:00:00');
        $timestamp = strtotime($scheduledTime);

        if (!wp_next_scheduled(self::SCHEDULE_HOOK)) {
            wp_schedule_event($timestamp, 'daily', self::SCHEDULE_HOOK);
        }

        add_action(self::SCHEDULE_HOOK, [$this, 'run_daily_tasks']);
    }

    public function run_daily_tasks()
    {
        $apiHandler = new ApiHandler();
        $log = $apiHandler->runScheduledTask();

        $this->log_results($log);
    }

    private function log_results(array $log)
    {
        $logMessage = "Daily tasks completed. " . implode(", ", $log);
        update_option(self::LOG_OPTION_KEY, $logMessage);
    }

    public static function get_log()
    {
        return get_option(self::LOG_OPTION_KEY, 'No logs available.');
    }

    public static function update_schedule_time($newTime)
    {
        update_option(self::SCHEDULE_TIME_OPTION_KEY, $newTime);

        // Clear existing schedule and set up a new one
        wp_clear_scheduled_hook(self::SCHEDULE_HOOK);
        wp_schedule_event(strtotime($newTime), 'daily', self::SCHEDULE_HOOK);
    }

    public static function get_next_run_time()
    {
        $timestamp = wp_next_scheduled(self::SCHEDULE_HOOK);
        if ($timestamp === false) {
            return 'Not scheduled';
        }
        return get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'Y-m-d H:i:s');
    }
}
