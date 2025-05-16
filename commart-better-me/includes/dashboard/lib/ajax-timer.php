<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Start timer for a step.
 * هنگام شروع تایمر، زمان شروع (timer_start) ثبت می‌شود و وضعیت دکمه (container_status) به "play" تغییر می‌کند.
 */
add_action('wp_ajax_commart_start_step', 'commart_start_step');
add_action('wp_ajax_nopriv_commart_start_step', 'commart_start_step');
function commart_start_step(){
    global $wpdb;
    if(empty($_POST['step_id'])){
        wp_send_json_error('Missing step ID.');
        wp_die();
    }
    $step_id = intval($_POST['step_id']);
    $steps_table = $wpdb->prefix . 'commart_better_me_steps';
    $current_time = current_time('mysql'); // ثبت زمان شروع به صورت MySQL
    $result = $wpdb->update($steps_table, array(
        'timer_start'      => $current_time,
        'status'           => 'in_progress',
        'container_status' => 'play'
    ), array('id' => $step_id));
    if(false !== $result){
        wp_send_json_success(array(
            'message'        => 'Step timer started',
            'timer_start'    => $current_time,
            'container_status' => 'play'
        ));
    } else {
        wp_send_json_error('Failed to start step.');
    }
}

/**
 * Pause timer for a step.
 * هنگام مکث تایمر، زمان سپری شده تا لحظه مکث در elapsed_time جمع می‌شود،
 * timer_start پاک شده و وضعیت دکمه (container_status) به "pause" تغییر می‌یابد.
 */
add_action('wp_ajax_commart_pause_step', 'commart_pause_step');
add_action('wp_ajax_nopriv_commart_pause_step', 'commart_pause_step');
function commart_pause_step(){
    global $wpdb;
    if(empty($_POST['step_id'])){
        wp_send_json_error('Missing step ID.');
        wp_die();
    }
    $step_id = intval($_POST['step_id']);
    $steps_table = $wpdb->prefix . 'commart_better_me_steps';
    // دریافت زمان شروع و مقدار فعلی elapsed_time از فیلد timer_start
    $step = $wpdb->get_row($wpdb->prepare("SELECT timer_start, elapsed_time FROM $steps_table WHERE id = %d", $step_id));
    if(!$step || empty($step->timer_start)){
        wp_send_json_error('No active timer to pause.');
        wp_die();
    }
    $start_time = strtotime($step->timer_start);
    $now = current_time('timestamp');
    $diff = $now - $start_time; // ثانیه‌های سپری شده از زمان شروع
    $new_elapsed = intval($step->elapsed_time) + $diff; // اضافه کردن به زمان سپری شده قبلی
    // به‌روزرسانی مرحله: ذخیره زمان سپری شده، پاکسازی timer_start، تغییر وضعیت به paused و ثبت container_status به "pause"
    $result = $wpdb->update($steps_table, array(
       'elapsed_time'     => $new_elapsed,
       'timer_start'      => null,
       'status'           => 'paused',
       'container_status' => 'pause'
    ), array('id' => $step_id));
    if(false !== $result){
        wp_send_json_success(array(
           'message'          => 'Step timer paused',
           'elapsed'          => $new_elapsed,
           'container_status' => 'pause'
        ));
    } else {
        wp_send_json_error('Failed to pause step.');
    }
}

/**
 * Stop timer for a step.
 * هنگام توقف تایمر، زمان نهایی محاسبه شده در elapsed_time ثبت می‌شود،
 * timer_start پاک شده و وضعیت به completed تغییر می‌یابد.
 * همچنین در صورت ارسال گزارش، آن نیز ذخیره می‌شود.
 */
add_action('wp_ajax_commart_stop_step', 'commart_stop_step');
add_action('wp_ajax_nopriv_commart_stop_step', 'commart_stop_step');
function commart_stop_step(){
    global $wpdb;
    if(empty($_POST['step_id'])){
        wp_send_json_error('Missing step ID.');
        wp_die();
    }
    $step_id = intval($_POST['step_id']);
    $report = isset($_POST['report']) ? sanitize_textarea_field($_POST['report']) : '';
    $steps_table = $wpdb->prefix . 'commart_better_me_steps';
    $step = $wpdb->get_row($wpdb->prepare("SELECT timer_start, elapsed_time FROM $steps_table WHERE id = %d", $step_id));
    if(!$step){
        wp_send_json_error('Step not found.');
        wp_die();
    }
    $new_elapsed = intval($step->elapsed_time);
    if(!empty($step->timer_start)){
        $start_time = strtotime($step->timer_start);
        $now = current_time('timestamp');
        $diff = $now - $start_time;
        $new_elapsed += $diff;
    }
    $result = $wpdb->update($steps_table, array(
       'elapsed_time'     => $new_elapsed,
       'timer_start'      => null,
       'status'           => 'completed',
       'report'           => $report,
       'container_status' => 'completed'
    ), array('id' => $step_id));
    if(false !== $result){
        $hrs = floor($new_elapsed / 3600);
        $mins = floor(($new_elapsed % 3600) / 60);
        $secs = $new_elapsed % 60;
        $formatted = sprintf("%02d:%02d:%02d", $hrs, $mins, $secs);
        wp_send_json_success(array(
           'message'           => 'Step timer stopped',
           'elapsed'           => $new_elapsed,
           'elapsed_formatted' => $formatted,
           'report'            => $report,
           'container_status'  => 'completed'
        ));
    } else {
        wp_send_json_error('Failed to stop step.');
    }
}
?>