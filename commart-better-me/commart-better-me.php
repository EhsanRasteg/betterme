<?php
/**
 * Plugin Name: Commart Better Me
 * Plugin URI: https://example.com/
 * Description: A custom plugin to add a management menu with Contacts, Campaign, License sections, a dashboard shortcode, and several custom database tables.
 * Version: 1.1.2
 * Author: CommartEhsan2
 * Author URI: https://example.com/
 * Text Domain: commart-better-me
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $commart_db_version;
$commart_db_version = '1.0';

// Define a constant for plugin URL if not defined
if ( ! defined( 'COMMART_BETTER_ME_PLUGIN_URL' ) ) {
    define( 'COMMART_BETTER_ME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-projects.php';

function commart_better_me_install() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table names with WordPress prefix
    $targets_table         = $wpdb->prefix . 'commart_better_me_targets';
    $plans_table           = $wpdb->prefix . 'commart_better_me_plans';
    $steps_table           = $wpdb->prefix . 'commart_better_me_steps';
    $step_reports_table    = $wpdb->prefix . 'commart_better_me_step_reports';
    $employers_table       = $wpdb->prefix . 'commart_better_me_employers';
    $employer_brands_table = $wpdb->prefix . 'commart_better_me_employer_brands';
    $projects_table        = $wpdb->prefix . 'commart_better_me_projects';
    $tasks_table           = $wpdb->prefix . 'commart_better_me_tasks';
    $tasks_reports_table   = $wpdb->prefix . 'commart_better_me_task_reports';
    $employer_profiles_table = $wpdb->prefix . 'commart_better_me_employer_profiles';

    // Define $files_table if it exists (adjust table name as necessary)
    $files_table = $wpdb->prefix . 'commart_better_me_files';

    // SQL to create tables
    $sql = "
    CREATE TABLE $targets_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        start_date DATE NOT NULL,
        deadline DATE NOT NULL,
        type ENUM('short', 'medium', 'long') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE $plans_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        target_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        description TEXT NOT NULL,
        budget DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (target_id) REFERENCES $targets_table(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE $steps_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        plan_id BIGINT(20) UNSIGNED NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL,
        timer_start DATETIME DEFAULT NULL,
        deadline DATE NOT NULL,
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        elapsed_time INT DEFAULT 0,
        report TEXT,
        container_status ENUM('play', 'pause') DEFAULT 'pause',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (plan_id) REFERENCES $plans_table(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE $step_reports_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        step_id BIGINT(20) UNSIGNED NOT NULL,
        description TEXT NOT NULL,
        cost DECIMAL(10,2) NOT NULL,
        attached_file BIGINT(20) UNSIGNED DEFAULT NULL,
        reported_by BIGINT(20) UNSIGNED NOT NULL,
        reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (step_id) REFERENCES $steps_table(id) ON DELETE CASCADE,
        FOREIGN KEY (attached_file) REFERENCES $files_table(id) ON DELETE SET NULL,
        FOREIGN KEY (reported_by) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE $employers_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_username VARCHAR(255) NOT NULL,
        employer_name VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        activity_field VARCHAR(255) NOT NULL,
        brands TEXT,
        business_mobile VARCHAR(20) NOT NULL,
        site VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        created_by BIGINT(20) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (created_by) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE $employer_brands_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_id BIGINT(20) UNSIGNED NOT NULL,
        brand_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (employer_id) REFERENCES $employers_table(id) ON DELETE CASCADE
    ) $charset_collate;

    CREATE TABLE $projects_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        projects_id VARCHAR(36) NOT NULL UNIQUE,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        projects_title VARCHAR(255) NOT NULL,
        brand VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        deadline DATE NOT NULL,
        status ENUM('in_progress', 'stopped', 'done') NOT NULL,
        description TEXT NOT NULL,
        project_amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;
    
  CREATE TABLE $tasks_table (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    project_id BIGINT(20) UNSIGNED NOT NULL,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    timer_start DATETIME DEFAULT NULL,
    deadline DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'paused', 'completed') DEFAULT 'pending',
    elapsed_time INT DEFAULT 0,
    report TEXT,
    container_status ENUM('play', 'pause', 'completed') DEFAULT 'pause',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (project_id) REFERENCES $projects_table(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
) $charset_collate;

    CREATE TABLE $employer_profiles_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        employer_username VARCHAR(255) NOT NULL,
        employer_name VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        activity_field VARCHAR(255) NOT NULL,
        brands TEXT,
        business_mobile VARCHAR(20) NOT NULL,
        site VARCHAR(255),
        email VARCHAR(255) NOT NULL,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        created_by BIGINT(20) UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'commart_better_me_install');
/**
 * Function to insert user details into the custom table upon login.
 */
function commart_better_me_store_user_details( $user_login, $user ) {
    global $wpdb;
    $table_users = $wpdb->prefix . 'commart_better_me';
    // Check if a record for this user already exists.
    $existing = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM $table_users WHERE user_id = %d", $user->ID));
    if ( $existing == 0 ) {
        // Retrieve the profile image URL (using Gravatar as default)
        $profile_image = get_avatar_url($user->ID);
        $wpdb->insert(
            $table_users,
            array(
                'user_id'       => $user->ID,
                'user_login'    => $user->user_login,
                'email'         => $user->user_email,
                'profile_image' => $profile_image,
            ),
            array('%d', '%s', '%s', '%s')
        );
    }
}
add_action('wp_login', 'commart_better_me_store_user_details', 10, 2);

// Include the AJAX timer functions.
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-timer.php';



// Include the AJAX chat handler file.
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-chat-handler.php';
// Include the general AJAX functions for non-chat content.
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/dashboard/lib/ajax-task.php';



class CommartBetterMe {

    public function __construct() {
        // Hooks
        add_action('admin_menu', [ $this, 'add_admin_menu' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_assets' ]);
        add_shortcode('commart-better-me', [ $this, 'shortcode_dashboard' ]);
    }

    public function add_admin_menu() {
        // Add main menu
        add_menu_page(
            __( 'Better Me', 'commart-better-me' ),
            __( 'Better Me', 'commart-better-me' ),
            'manage_options',
            'commart-better-me',
            [ $this, 'load_dashboard_page' ],
            'dashicons-admin-generic',
            6
        );
        // Add submenus
        add_submenu_page(
            'commart-better-me',
            __( 'Contacts', 'commart-better-me' ),
            __( 'Contacts', 'commart-better-me' ),
            'manage_options',
            'commart-contacts',
            [ $this, 'load_contacts_page' ]
        );
        add_submenu_page(
            'commart-better-me',
            __( 'Campaign', 'commart-better-me' ),
            __( 'Campaign', 'commart-better-me' ),
            'manage_options',
            'commart-campaign',
            [ $this, 'load_campaign_page' ]
        );
        add_submenu_page(
            'commart-better-me',
            __( 'License', 'commart-better-me' ),
            __( 'License', 'commart-better-me' ),
            'manage_options',
            'commart-license',
            [ $this, 'load_license_page' ]
        );
    }

    public function enqueue_assets( $hook ) {
        // Load CSS and JS only for plugin pages in admin panel
        $plugin_pages = [ 'toplevel_page_commart-better-me', 'commart-contacts', 'commart-campaign', 'commart-license' ];
        if ( in_array($hook, $plugin_pages) ) {
            wp_enqueue_style(
                'commart-better-me-styles',
                plugins_url('includes/css/styles.css', __FILE__)
            );
            // Enqueue chat style file from the specified path.
            wp_enqueue_style(
                'commart-chat-style',
                plugins_url('includes/css/chatstyle.css', __FILE__)
            );
            wp_enqueue_script(
                'commart-better-me-scripts',
                plugins_url('includes/js/scripts.js', __FILE__),
                ['jquery'],
                null,
                true
            );
            // Enqueue the timer script for steps
            wp_enqueue_script(
                'commart-timer-script',
                plugins_url('includes/dashboard/lib/timer-script.js', __FILE__),
                ['jquery'],
                null,
                true
            );
            
            
            
           
        }
    }

    public function load_dashboard_page() {
        include plugin_dir_path(__FILE__) . 'includes/dashboard/commart-dashboard.php';
    }

    public function load_contacts_page() {
        include plugin_dir_path(__FILE__) . 'includes/commart-contacts.php';
    }

    public function load_campaign_page() {
        include plugin_dir_path(__FILE__) . 'includes/commart-campaign.php';
    }

    public function load_license_page() {
        include plugin_dir_path(__FILE__) . 'includes/logfile.php';
    }

    public function shortcode_dashboard() {
        
        // Enqueue the timer script for steps – existing code.
wp_enqueue_script(
    'commart-timer-script',
    plugins_url('includes/dashboard/lib/timer-script.js', __FILE__),
    ['jquery'],
    null,
    true
);

// Enqueue the timer script for tasks with a unique handle to avoid conflicts
wp_enqueue_script(
    'commart-task-timer-script',
    plugins_url('includes/dashboard/lib/task-timer-script.js', __FILE__),
    ['jquery'],
    null,
    true
);

// Enqueue the AJAX script for tasks
wp_enqueue_script(
    'commart-task-script',
    plugins_url('includes/dashboard/lib/task-script.js', __FILE__),
    array('jquery'),
    null,
    true
);


        // Enqueue dashboard style for front end (for shortcode output)
        wp_enqueue_style(
            'commart-dashboard-style',
            COMMART_BETTER_ME_PLUGIN_URL . 'includes/css/dashboard.css'
        );
        // Enqueue chat style file for the frontend.
        wp_enqueue_style(
            'commart-chat-style',
            COMMART_BETTER_ME_PLUGIN_URL . 'includes/css/chatstyle.css'
        );
        if ( ! is_user_logged_in() ) {
            $login_register_file = plugin_dir_path(__FILE__) . 'includes/commart-login-register.php';
            if ( file_exists($login_register_file) ) {
                ob_start();
                include $login_register_file;
                return ob_get_clean();
            } else {
                return '<p>' . esc_html__('Login/Register file not found.', 'commart-better-me') . '</p>';
            }
        }
        $dashboard_file = plugin_dir_path(__FILE__) . 'includes/dashboard/commart-dashboard.php';
        if ( file_exists($dashboard_file) ) {
            ob_start();
            include $dashboard_file;
            return ob_get_clean();
        } else {
            return '<p>' . esc_html__('Dashboard file not found.', 'commart-better-me') . '</p>';
        }
    }
}



new CommartBetterMe();
?>
