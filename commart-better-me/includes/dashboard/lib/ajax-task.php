<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle add or update tasks.
 * Inserts a new task into the table WITHOUT starting the timer.
 */
add_action('wp_ajax_commart_add_update_tasks', 'commart_add_update_tasks');
add_action('wp_ajax_nopriv_commart_add_update_tasks', 'commart_add_update_tasks');
function commart_add_update_tasks(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    $data_str = wp_unslash($_POST['form_data']);
    parse_str($data_str, $form_data);

    $tasks_table    = $wpdb->prefix . 'commart_better_me_tasks';
    $projects_table = $wpdb->prefix . 'commart_better_me_projects';
    $current_user   = wp_get_current_user();

    // Insert the task with status pending, timer not started (tasks_timer_start remains NULL),
    // tasks_elapsed_time is initially 0, tasks_report is empty.
    $data = array(
        'user_id'          => $current_user->ID,
        'projects_id'      => intval($form_data['tasks_projects_id']),
        'tasks_title'      => sanitize_text_field($form_data['tasks_title']),
        'tasks_deadline'   => sanitize_text_field($form_data['tasks_deadline']),
        'tasks_status'     => 'pending',
        'tasks_created_at' => current_time('mysql')
    );
    $tasks_id = intval($form_data['tasks_id']);

    if($tasks_id){
        // When updating, don't change the creation time.
        unset($data['tasks_created_at']);
        $result = $wpdb->update($tasks_table, $data, array('id' => $tasks_id));
        if(false !== $result){
            $project = $wpdb->get_row($wpdb->prepare("SELECT projects_title FROM $projects_table WHERE id = %d", $data['projects_id']));
            $response = array(
                'id'                => $tasks_id,
                'projects_id'       => $data['projects_id'],
                'projects_title'    => $project ? $project->projects_title : '',
                'tasks_title'       => $data['tasks_title'],
                'tasks_deadline'    => $data['tasks_deadline'],
                'elapsed'           => 0,
                'elapsed_formatted' => "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('Failed to update task.');
        }
    } else {
        $result = $wpdb->insert($tasks_table, $data);
        if($result){
            $insert_id = $wpdb->insert_id;
            $project = $wpdb->get_row($wpdb->prepare("SELECT projects_title FROM $projects_table WHERE id = %d", $data['projects_id']));
            $response = array(
                'id'                => $insert_id,
                'projects_id'       => $data['projects_id'],
                'projects_title'    => $project ? $project->projects_title : '',
                'tasks_title'       => $data['tasks_title'],
                'tasks_deadline'    => $data['tasks_deadline'],
                'elapsed'           => 0,
                'elapsed_formatted' => "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('Failed to add new task.');
        }
    }
}

/**
 * Handle delete tasks.
 */
add_action('wp_ajax_commart_delete_tasks', 'commart_delete_tasks');
add_action('wp_ajax_nopriv_commart_delete_tasks', 'commart_delete_tasks');
function commart_delete_tasks(){
    global $wpdb;
    if(empty($_POST['tasks_id'])){
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    $tasks_id    = intval($_POST['tasks_id']);
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    $result      = $wpdb->delete($tasks_table, array('id' => $tasks_id));
    if($result){
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete task.');
    }
}

/**
 * Handle start tasks.
 * When the user presses the start button, update the task to set tasks_timer_start (as current datetime)
 * and change tasks_status to 'in_progress'. No timer calculation is done until stop.
 */
add_action('wp_ajax_commart_start_tasks', 'commart_start_tasks');
add_action('wp_ajax_nopriv_commart_start_tasks', 'commart_start_tasks');
// Implementation for starting tasks should be added here

/**
 * Handle stop tasks.
 * When stop is pressed, calculate elapsed time from the stored tasks_timer_start until now,
 * update the tasks_status to 'completed', clear the tasks_timer_start (set to NULL),
 * and update the tasks_report field with the passed report.
 */
add_action('wp_ajax_commart_stop_tasks', 'commart_stop_tasks');
add_action('wp_ajax_nopriv_commart_stop_tasks', 'commart_stop_tasks');
// Implementation for stopping tasks should be added here

/**
 * Handle update tasks report.
 */
add_action('wp_ajax_commart_update_tasks_report', 'commart_update_tasks_report');
add_action('wp_ajax_nopriv_commart_update_tasks_report', 'commart_update_tasks_report');
function commart_update_tasks_report(){
    global $wpdb;
    if(empty($_POST['tasks_id'])){
        wp_send_json_error('Missing task ID.');
        wp_die();
    }
    if(!isset($_POST['report'])){
        wp_send_json_error('Missing report content.');
        wp_die();
    }
    $tasks_id    = intval($_POST['tasks_id']);
    $report      = sanitize_textarea_field($_POST['report']);
    $tasks_table = $wpdb->prefix . 'commart_better_me_tasks';
    $result      = $wpdb->update($tasks_table, array('tasks_report' => $report), array('id' => $tasks_id));
    if(false !== $result){
        wp_send_json_success(array('report' => $report));
    } else {
        wp_send_json_error('Failed to update report.');
    }
}
?>