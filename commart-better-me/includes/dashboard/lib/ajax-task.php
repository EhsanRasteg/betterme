<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$tasks_table = $wpdb->prefix . 'commart_better_me_tasks';

/**
 * شروع تایمر تسک.
 */
add_action('wp_ajax_commart_start_task', 'commart_start_task');
add_action('wp_ajax_nopriv_commart_start_task', 'commart_start_task');
function commart_start_task(){
    global $wpdb;
    if(empty($_POST['task_id'])){
        wp_send_json_error('شناسه تسک موجود نیست.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $current_time = current_time('mysql');
    $result = $wpdb->update($wpdb->prefix . 'commart_better_me_tasks', array(
        'timer_start'      => $current_time,
        'status'           => 'in_progress',
        'container_status' => 'play'
    ), array('id' => $task_id));
    if(false !== $result){
        wp_send_json_success(array(
            'message'        => 'تسک شروع شد',
            'timer_start'    => $current_time,
            'container_status' => 'play'
        ));
    } else {
        wp_send_json_error('شروع تسک موفقیت‌آمیز نبود.');
    }
}

/**
 * مکث تایمر تسک.
 */
add_action('wp_ajax_commart_pause_task', 'commart_pause_task');
add_action('wp_ajax_nopriv_commart_pause_task', 'commart_pause_task');
function commart_pause_task(){
    global $wpdb;
    if(empty($_POST['task_id'])){
        wp_send_json_error('شناسه تسک موجود نیست.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $task = $wpdb->get_row($wpdb->prepare("SELECT timer_start, elapsed_time FROM $tasks_table WHERE id = %d", $task_id));
    if(!$task || empty($task->timer_start)){
        wp_send_json_error('تایمر در حال اجرا برای این تسک موجود نیست.');
        wp_die();
    }
    $start_time = strtotime($task->timer_start);
    $now = current_time('timestamp');
    $diff = $now - $start_time;
    $new_elapsed = intval($task->elapsed_time) + $diff;
    $result = $wpdb->update($tasks_table, array(
       'elapsed_time'     => $new_elapsed,
       'timer_start'      => null,
       'status'           => 'paused',
       'container_status' => 'pause'
    ), array('id' => $task_id));
    if(false !== $result){
        wp_send_json_success(array(
           'message'          => 'تایمر تسک مکث شد',
           'elapsed'          => $new_elapsed,
           'container_status' => 'pause'
        ));
    } else {
        wp_send_json_error('متوقف کردن تسک موفقیت‌آمیز نبود.');
    }
}

/**
 * توقف نهایی تسک (تایمر متوقف شده و در صورت دریافت گزارش ثبت می‌شود).
 */
add_action('wp_ajax_commart_stop_task', 'commart_stop_task');
add_action('wp_ajax_nopriv_commart_stop_task', 'commart_stop_task');
function commart_stop_task(){
    global $wpdb;
    if(empty($_POST['task_id'])){
        wp_send_json_error('شناسه تسک موجود نیست.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $report = isset($_POST['report']) ? sanitize_textarea_field($_POST['report']) : '';
    $task = $wpdb->get_row($wpdb->prepare("SELECT timer_start, elapsed_time FROM $tasks_table WHERE id = %d", $task_id));
    if(!$task){
        wp_send_json_error('تسک یافت نشد.');
        wp_die();
    }
    $new_elapsed = intval($task->elapsed_time);
    if(!empty($task->timer_start)){
        $start_time = strtotime($task->timer_start);
        $now = current_time('timestamp');
        $diff = $now - $start_time;
        $new_elapsed += $diff;
    }
    $result = $wpdb->update($tasks_table, array(
       'elapsed_time'     => $new_elapsed,
       'timer_start'      => null,
       'status'           => 'completed',
       'report'           => $report,
       'container_status' => 'completed'
    ), array('id' => $task_id));
    if(false !== $result){
        $hrs = floor($new_elapsed / 3600);
        $mins = floor(($new_elapsed % 3600) / 60);
        $secs = $new_elapsed % 60;
        $formatted = sprintf("%02d:%02d:%02d", $hrs, $mins, $secs);
        wp_send_json_success(array(
           'message'           => 'تسک متوقف شد',
           'elapsed'           => $new_elapsed,
           'elapsed_formatted' => $formatted,
           'report'            => $report,
           'container_status'  => 'completed'
        ));
    } else {
        wp_send_json_error('توقف تسک موفقیت‌آمیز نبود.');
    }
}

/**
 * افزودن یا به‌روزرسانی تسک.
 */
add_action('wp_ajax_commart_add_update_task', 'commart_add_update_task');
add_action('wp_ajax_nopriv_commart_add_update_task', 'commart_add_update_task');
function commart_add_update_task(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('اطلاعات فرم موجود نیست.');
        wp_die();
    }
    $data_str = wp_unslash($_POST['form_data']);
    parse_str($data_str, $form_data);
    
    $current_user = wp_get_current_user();
    $data = array(
        'user_id'    => $current_user->ID,
        'project_id' => intval($form_data['task_project_id']),
        'title'      => sanitize_text_field($form_data['task_title']),
        'deadline'   => sanitize_text_field($form_data['task_deadline']),
        'status'     => 'pending',
        'created_at' => current_time('mysql')
    );
    $task_id = intval($form_data['task_id']);
    
    if($task_id){
        unset($data['created_at']);
        $result = $wpdb->update($tasks_table, $data, array('id' => $task_id));
        if(false !== $result){
            // واکشی عنوان پروژه برای نمایش
            $project = $wpdb->get_row($wpdb->prepare("SELECT projects_title FROM $wpdb->prefix.commart_better_me_projects WHERE id = %d", $data['project_id']));
            $response = array(
                'id'                => $task_id,
                'project_id'        => $data['project_id'],
                'project_title'     => $project ? $project->projects_title : '',
                'title'             => $data['title'],
                'deadline'          => $data['deadline'],
                'elapsed'           => 0,
                'elapsed_formatted' => "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('به‌روزرسانی تسک انجام نشد.');
        }
    } else {
        $result = $wpdb->insert($tasks_table, $data);
        if($result){
            $insert_id = $wpdb->insert_id;
            $project = $wpdb->get_row($wpdb->prepare("SELECT projects_title FROM $wpdb->prefix.commart_better_me_projects WHERE id = %d", $data['project_id']));
            $response = array(
                'id'                => $insert_id,
                'project_id'        => $data['project_id'],
                'project_title'     => $project ? $project->projects_title : '',
                'title'             => $data['title'],
                'deadline'          => $data['deadline'],
                'elapsed'           => 0,
                'elapsed_formatted' => "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('تسک جدید افزوده نشد.');
        }
    }
}

/**
 * حذف تسک.
 */
add_action('wp_ajax_commart_delete_task', 'commart_delete_task');
add_action('wp_ajax_nopriv_commart_delete_task', 'commart_delete_task');
function commart_delete_task(){
    global $wpdb;
    if(empty($_POST['task_id'])){
        wp_send_json_error('شناسه تسک موجود نیست.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $result = $wpdb->delete($tasks_table, array('id'=>$task_id));
    if($result){
        wp_send_json_success();
    } else {
        wp_send_json_error('حذف تسک انجام نشد.');
    }
}

/**
 * به‌روزرسانی گزارش تسک.
 */
add_action('wp_ajax_commart_update_task_report', 'commart_update_task_report');
add_action('wp_ajax_nopriv_commart_update_task_report', 'commart_update_task_report');
function commart_update_task_report(){
    global $wpdb;
    if(empty($_POST['task_id'])){
        wp_send_json_error('شناسه تسک موجود نیست.');
        wp_die();
    }
    if(!isset($_POST['report'])){
        wp_send_json_error('متن گزارش موجود نیست.');
        wp_die();
    }
    $task_id = intval($_POST['task_id']);
    $report = sanitize_textarea_field($_POST['report']);
    $result = $wpdb->update($tasks_table, array(
        'report' => $report
    ), array('id'=>$task_id));
    if(false !== $result){
        wp_send_json_success(array('report' => $report));
    } else {
        wp_send_json_error('به‌روزرسانی گزارش انجام نشد.');
    }
}
?>
