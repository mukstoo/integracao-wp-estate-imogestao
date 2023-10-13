<?php
/**
 * Plugin Name: Best Integracao Imogestao
 * Description: A plugin to fetch data from an external API and populate the WP database.
 * Version: 1.0
 * Author: Your Name
 */

namespace BestIntegracaoImogestao;

// Exit if accessed directly
defined('ABSPATH') or exit;

// Include the Admin class
require_once __DIR__ . '/admin/AdminSettings.php';

// Include the classes in the includes folder
require_once __DIR__ . '/includes/ApiHandler.php';
require_once __DIR__ . '/includes/PostManager.php';
require_once __DIR__ . '/includes/ImageManager.php';

// Include the new Scheduler class
require_once __DIR__ . '/includes/Scheduler.php';

class MyPlugin
{
    private \BestIntegracaoImogestao\Admin\AdminSettings $admin;

    public function __construct()
    {
        $this->admin = new Admin\AdminSettings();
    }

    public function run(): void
    {
        $this->admin->init();
        add_action('init', [$this, 'registerLitoralTaxonomy']);
    }

    public function registerLitoralTaxonomy(): void
    {
        register_taxonomy(
            'litoral',
            'estate_property',
            [
                'label' => __('Litoral'),
                'rewrite' => ['slug' => 'litoral'],
                'hierarchical' => false,
            ]
        );
    }
}

$myPlugin = new MyPlugin();
$myPlugin->run();
