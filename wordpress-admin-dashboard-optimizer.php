<?php
/**
 * Plugin Name: WordPress Admin Dashboard Optimizer (WPADO)
 * Plugin URI: https://github.com/jaureguijoseph/wpado
 * Description: Secure gift card liquidation platform with Plaid RTP/FedNow payouts and federal compliance limits. Integrates with WS Form PRO and Authorize.Net for complete user workflow management.
 * Version: 1.0.0
 * Author: Joseph Jauregui
 * Author URI: https://github.com/jaureguijoseph
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpado
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8.2
 * Requires PHP: 8.0
 * Network: false
 *
 * WPADO is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPADO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPADO_VERSION', '1.0.0');
define('WPADO_PLUGIN_FILE', __FILE__);
define('WPADO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPADO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPADO_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Required WordPress version check
function wpado_check_wp_version() {
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('WordPress Admin Dashboard Optimizer requires WordPress 6.0 or higher.', 'wpado');
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

// Required PHP version check
function wpado_check_php_version() {
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('WordPress Admin Dashboard Optimizer requires PHP 8.0 or higher.', 'wpado');
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

// Check for required plugins
function wpado_check_required_plugins() {
    $required_plugins = [
        'ws-form/ws-form.php' => 'WS Form PRO',
        'ws-form-authorize/ws-form-authorize.php' => 'WS Form PRO - Authorize Accept',
        'ws-form-user/ws-form-user.php' => 'WS Form PRO - User Management',
        'jet-engine/jet-engine.php' => 'JetEngine'
    ];
    
    $missing_plugins = [];
    
    foreach ($required_plugins as $plugin_file => $plugin_name) {
        if (!is_plugin_active($plugin_file)) {
            $missing_plugins[] = $plugin_name;
        }
    }
    
    if (!empty($missing_plugins)) {
        add_action('admin_notices', function() use ($missing_plugins) {
            echo '<div class="notice notice-error"><p>';
            echo __('WordPress Admin Dashboard Optimizer requires the following plugins to be active: ', 'wpado');
            echo implode(', ', $missing_plugins);
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Main Plugin Class
 */
class WPADO_Plugin {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Check system requirements
        if (!wpado_check_wp_version() || !wpado_check_php_version()) {
            return;
        }
        
        // Initialize plugin
        add_action('plugins_loaded', [$this, 'init']);
        
        // Activation/Deactivation hooks
        register_activation_hook(WPADO_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(WPADO_PLUGIN_FILE, [$this, 'deactivate']);
        
        // Uninstall hook
        register_uninstall_hook(WPADO_PLUGIN_FILE, ['WPADO_Plugin', 'uninstall']);
        
        // Plugin action links
        add_filter('plugin_action_links_' . WPADO_PLUGIN_BASENAME, [$this, 'plugin_action_links']);
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check required plugins
        if (!wpado_check_required_plugins()) {
            return;
        }
        
        // Load text domain
        load_plugin_textdomain('wpado', false, dirname(WPADO_PLUGIN_BASENAME) . '/languages');
        
        // Load Composer autoloader if it exists
        if (file_exists(WPADO_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once WPADO_PLUGIN_DIR . 'vendor/autoload.php';
        }
        
        // Initialize core components
        $this->load_includes();
        $this->init_hooks();
        
        do_action('wpado_loaded');
    }
    
    /**
     * Load required files
     */
    private function load_includes() {
        // Core components will be loaded here
        // This will be expanded in subsequent phases
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        }
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this, 'frontend_enqueue_scripts']);
        
        // AJAX hooks will be added here
        // WS Form hooks will be added here
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check requirements one more time
        if (!wpado_check_wp_version() || !wpado_check_php_version()) {
            wp_die(
                __('WordPress Admin Dashboard Optimizer requires WordPress 6.0+ and PHP 8.0+', 'wpado'),
                __('Plugin Activation Error', 'wpado'),
                ['back_link' => true]
            );
        }
        
        // Create database tables
        $this->create_database_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Create custom user roles
        $this->create_custom_roles();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        update_option('wpado_activation_time', time());
        update_option('wpado_version', WPADO_VERSION);
        
        do_action('wpado_activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Remove custom user roles
        $this->remove_custom_roles();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('wpado_deactivated');
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove all plugin data
        // This will be implemented when we add cleanup functionality
        
        do_action('wpado_uninstalled');
    }
    
    /**
     * Create custom database tables
     */
    private function create_database_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Transactions table
        $transactions_table = $wpdb->prefix . 'wpado_plugin_transactions';
        $transactions_sql = "CREATE TABLE $transactions_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            invoice_number VARCHAR(255) NOT NULL,
            gross_amount DECIMAL(10,2) NOT NULL,
            net_payout_amount DECIMAL(10,2) NOT NULL,
            fee_percentage DECIMAL(5,2) NOT NULL,
            flat_fee DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            payout_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            payout_method VARCHAR(50) DEFAULT NULL,
            reconciliation_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            additional_metadata TEXT,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY date_created (date_created),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Error logs table
        $error_logs_table = $wpdb->prefix . 'wpado_plugin_error_logs';
        $error_logs_sql = "CREATE TABLE $error_logs_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            error_code VARCHAR(50) NOT NULL,
            error_message TEXT NOT NULL,
            error_data LONGTEXT,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            phase_error_occurred_in VARCHAR(50) DEFAULT NULL,
            date_error_occurred DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            additional_metadata TEXT,
            PRIMARY KEY (id),
            KEY error_code (error_code),
            KEY user_id (user_id),
            KEY date_error_occurred (date_error_occurred),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL
        ) $charset_collate;";
        
        // Payout logs table
        $payout_logs_table = $wpdb->prefix . 'wpado_plugin_payout_log';
        $payout_logs_sql = "CREATE TABLE $payout_logs_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            transaction_id BIGINT(20) UNSIGNED NOT NULL,
            invoice_number VARCHAR(255) NOT NULL,
            transaction_amount DECIMAL(10,2) NOT NULL,
            payout_amount DECIMAL(10,2) NOT NULL,
            payout_method VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            payout_date DATETIME DEFAULT NULL,
            payout_bank_name VARCHAR(255) DEFAULT NULL,
            retry_count INT(3) NOT NULL DEFAULT 0,
            next_retry_date DATETIME DEFAULT NULL,
            additional_metadata TEXT,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY transaction_id (transaction_id),
            KEY status (status),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            FOREIGN KEY (transaction_id) REFERENCES $transactions_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($transactions_sql);
        dbDelta($error_logs_sql);
        dbDelta($payout_logs_sql);
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = [
            'wpado_encryption_key' => wp_generate_password(64, true, true),
            'wpado_encryption_key_rotation_date' => date('Y-m-d', strtotime('+90 days')),
            'wpado_federal_limits' => [
                'last_24_hours' => 500,
                'last_7_days' => 1500,
                'month_to_date' => 2500,
                'year_to_date' => 8500
            ],
            'wpado_role_expiry_time' => 1800, // 30 minutes
            'wpado_retry_attempts' => 3,
            'wpado_retry_interval' => 900, // 15 minutes
            'wpado_data_retention_days' => 90,
            'wpado_plaid_environment' => 'sandbox',
            'wpado_debug_mode' => false
        ];
        
        foreach ($default_options as $option_name => $option_value) {
            if (!get_option($option_name)) {
                update_option($option_name, $option_value);
            }
        }
    }
    
    /**
     * Create custom user roles
     */
    private function create_custom_roles() {
        // Plaid User role
        add_role('plaid_user', __('Plaid User', 'wpado'), [
            'read' => true,
            'wpado_link_bank_account' => true
        ]);
        
        // Transaction User role
        add_role('transaction_user', __('Transaction User', 'wpado'), [
            'read' => true,
            'wpado_process_transactions' => true
        ]);
        
        // PAYMENT role (temporary payout role)
        add_role('payment_user', __('Payment User', 'wpado'), [
            'read' => true,
            'wpado_request_payout' => true
        ]);
        
        // Add capabilities to administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('wpado_manage_settings');
            $admin_role->add_cap('wpado_view_transactions');
            $admin_role->add_cap('wpado_view_error_logs');
            $admin_role->add_cap('wpado_manage_payouts');
        }
    }
    
    /**
     * Remove custom user roles
     */
    private function remove_custom_roles() {
        remove_role('plaid_user');
        remove_role('transaction_user');
        remove_role('payment_user');
        
        // Remove capabilities from administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->remove_cap('wpado_manage_settings');
            $admin_role->remove_cap('wpado_view_transactions');
            $admin_role->remove_cap('wpado_view_error_logs');
            $admin_role->remove_cap('wpado_manage_payouts');
        }
    }
    
    /**
     * Admin menu
     */
    public function admin_menu() {
        add_menu_page(
            __('WPADO Dashboard', 'wpado'),
            __('WPADO', 'wpado'),
            'wpado_manage_settings',
            'wpado-dashboard',
            [$this, 'admin_dashboard_page'],
            'dashicons-money-alt',
            30
        );
        
        add_submenu_page(
            'wpado-dashboard',
            __('Settings', 'wpado'),
            __('Settings', 'wpado'),
            'wpado_manage_settings',
            'wpado-settings',
            [$this, 'admin_settings_page']
        );
        
        add_submenu_page(
            'wpado-dashboard',
            __('Transactions', 'wpado'),
            __('Transactions', 'wpado'),
            'wpado_view_transactions',
            'wpado-transactions',
            [$this, 'admin_transactions_page']
        );
        
        add_submenu_page(
            'wpado-dashboard',
            __('Error Logs', 'wpado'),
            __('Error Logs', 'wpado'),
            'wpado_view_error_logs',
            'wpado-error-logs',
            [$this, 'admin_error_logs_page']
        );
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_dashboard_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('WPADO Dashboard', 'wpado') . '</h1>';
        echo '<p>' . __('WordPress Admin Dashboard Optimizer - Gift Card Liquidation Platform', 'wpado') . '</p>';
        echo '<div class="notice notice-info"><p>' . __('Phase 1: Plugin foundation completed. Additional features will be implemented in subsequent phases.', 'wpado') . '</p></div>';
        echo '</div>';
    }
    
    /**
     * Admin settings page
     */
    public function admin_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('WPADO Settings', 'wpado') . '</h1>';
        echo '<p>' . __('Settings will be implemented in Phase 5.', 'wpado') . '</p>';
        echo '</div>';
    }
    
    /**
     * Admin transactions page
     */
    public function admin_transactions_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('WPADO Transactions', 'wpado') . '</h1>';
        echo '<p>' . __('Transaction management will be implemented in subsequent phases.', 'wpado') . '</p>';
        echo '</div>';
    }
    
    /**
     * Admin error logs page
     */
    public function admin_error_logs_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('WPADO Error Logs', 'wpado') . '</h1>';
        echo '<p>' . __('Error log viewing will be implemented in subsequent phases.', 'wpado') . '</p>';
        echo '</div>';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'wpado') !== false) {
            wp_enqueue_style('wpado-admin-css', WPADO_PLUGIN_URL . 'assets/css/admin.css', [], WPADO_VERSION);
            wp_enqueue_script('wpado-admin-js', WPADO_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WPADO_VERSION, true);
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('wpado-frontend-css', WPADO_PLUGIN_URL . 'assets/css/frontend.css', [], WPADO_VERSION);
        wp_enqueue_script('wpado-frontend-js', WPADO_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], WPADO_VERSION, true);
        
        // Localize script with AJAX URL and nonce
        wp_localize_script('wpado-frontend-js', 'wpado_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpado_nonce'),
            'user_id' => get_current_user_id()
        ]);
    }
    
    /**
     * Add plugin action links
     */
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wpado-settings') . '">' . __('Settings', 'wpado') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize the plugin
function wpado_init() {
    return WPADO_Plugin::get_instance();
}

// Start the plugin
add_action('init', 'wpado_init');

/**
 * Helper function to get plugin instance
 */
function wpado() {
    return WPADO_Plugin::get_instance();
}