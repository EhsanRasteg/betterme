<?php
// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle AJAX for listing projects.
 */
add_action('wp_ajax_commart_list_projects', 'commart_list_projects');
add_action('wp_ajax_nopriv_commart_list_projects', 'commart_list_projects');
function commart_list_projects() {
    global $wpdb;
    $table = $wpdb->prefix . 'commart_better_me_projects';
    $current_user = wp_get_current_user();

    // Retrieve projects defined by the current user.
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $current_user->ID),
        ARRAY_A
    );
    if ($results) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No projects found.');
    }
    wp_die();
}

/**
 * Handle AJAX for adding a new project.
 */
add_action('wp_ajax_commart_add_project', 'commart_add_project');
add_action('wp_ajax_nopriv_commart_add_project', 'commart_add_project');
function commart_add_project() {
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    parse_str( wp_unslash($_POST['form_data']), $form_data );

    // Validate required fields.
    // Changed from 'project_title' to 'projects_title' for consistency.
    $required_fields = ['projects_title', 'brand', 'start_date', 'deadline', 'status', 'description', 'project_amount'];
    foreach ($required_fields as $field) {
        if ( empty( $form_data[$field] ) ) {
            wp_send_json_error( "Missing field: {$field}" );
            wp_die();
        }
    }

    // Sanitize and prepare data.
    // Using field name 'projects_title' per provided column details.
    $data = [
        'user_id'         => get_current_user_id(),
        'projects_title'  => sanitize_text_field( $form_data['projects_title'] ),
        'brand'           => sanitize_text_field( $form_data['brand'] ),
        'start_date'      => sanitize_text_field( $form_data['start_date'] ),
        'deadline'        => sanitize_text_field( $form_data['deadline'] ),
        'status'          => sanitize_text_field( $form_data['status'] ),
        'description'     => sanitize_textarea_field( $form_data['description'] ),
        'project_amount'  => floatval( $form_data['project_amount'] ),
        'created_at'      => current_time('mysql')
    ];
    $table = $wpdb->prefix . 'commart_better_me_projects';
    $result = $wpdb->insert( $table, $data );
    if ( $result ) {
        wp_send_json_success('Project added successfully!');
    } else {
        wp_send_json_error('Failed to add project.');
    }
    wp_die();
}

/**
 * Handle AJAX for updating a project.
 */
add_action('wp_ajax_commart_update_project', 'commart_update_project');
add_action('wp_ajax_nopriv_commart_update_project', 'commart_update_project');
function commart_update_project() {
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    parse_str( wp_unslash($_POST['form_data']), $form_data );
    
    if ( empty($form_data['project_id']) ) {
        wp_send_json_error('Missing project ID.');
        wp_die();
    }
    $project_id = intval($form_data['project_id']);
    // Validate required fields.
    // Changed required field name from 'project_title' to 'projects_title'
    $required_fields = ['projects_title', 'brand', 'start_date', 'deadline', 'status', 'description', 'project_amount'];
    foreach ($required_fields as $field) {
        if ( empty( $form_data[$field] ) ) {
            wp_send_json_error( "Missing field: {$field}" );
            wp_die();
        }
    }
    $data = [
        'projects_title'  => sanitize_text_field( $form_data['projects_title'] ),
        'brand'           => sanitize_text_field( $form_data['brand'] ),
        'start_date'      => sanitize_text_field( $form_data['start_date'] ),
        'deadline'        => sanitize_text_field( $form_data['deadline'] ),
        'status'          => sanitize_text_field( $form_data['status'] ),
        'description'     => sanitize_textarea_field( $form_data['description'] ),
        'project_amount'  => floatval( $form_data['project_amount'] )
    ];
    $table = $wpdb->prefix . 'commart_better_me_projects';
    $result = $wpdb->update( $table, $data, ['id' => $project_id] );
    if ( false !== $result ) {
        wp_send_json_success('Project updated successfully!');
    } else {
        wp_send_json_error('Failed to update project.');
    }
    wp_die();
}

/**
 * Handle AJAX for deleting a project.
 */
add_action('wp_ajax_commart_delete_project', 'commart_delete_project');
add_action('wp_ajax_nopriv_commart_delete_project', 'commart_delete_project');
function commart_delete_project() {
    global $wpdb;
    if ( empty($_POST['project_id']) ) {
        wp_send_json_error('Missing project ID.');
        wp_die();
    }
    $project_id = intval($_POST['project_id']);
    $table = $wpdb->prefix . 'commart_better_me_projects';
    $result = $wpdb->delete( $table, ['id' => $project_id] );
    if ( $result ) {
        wp_send_json_success('Project deleted successfully!');
    } else {
        wp_send_json_error('Failed to delete project.');
    }
    wp_die();
}

/**
 * (Optional) Handle AJAX for sending invoice.
 */
add_action('wp_ajax_commart_send_invoice', 'commart_send_invoice');
add_action('wp_ajax_nopriv_commart_send_invoice', 'commart_send_invoice');
function commart_send_invoice() {
    // Logic for sending invoice (eg. via email) goes here.
    wp_send_json_success('Invoice sent successfully!');
    wp_die();
}
?>