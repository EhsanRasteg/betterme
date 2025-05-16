<?php
/**
 * Handles AJAX request for loading Chat content.
 */
add_action('wp_ajax_load_chat', 'load_chat_content');
add_action('wp_ajax_nopriv_load_chat', 'load_chat_content');

function load_chat_content() {
    // درج مسیر فایل chat.php نسبت به موقعیت فعلی این فایل.
    // از آنجایی که این فایل در "includes/dashboard/lib/" قرار دارد،
    // برای رفتن به "includes/dashboard/chat.php" باید از "../chat.php" استفاده کنیم.
    include plugin_dir_path( __FILE__ ) . '../chat.php';
    wp_die(); // پایان عملیات AJAX
}

add_action('wp_ajax_check_user', 'commart_check_user_callback');
add_action('wp_ajax_nopriv_check_user', 'commart_check_user_callback');

function commart_check_user_callback() {
    if ( ! isset($_POST['username']) ) {
        wp_send_json_error(array('message' => 'Username not provided'));
        wp_die();
    }
    $username = sanitize_text_field($_POST['username']);
    $user = get_user_by('login', $username);
    if ( $user ) {
        $avatar = get_avatar_url( $user->ID );
        wp_send_json_success(array(
            'username' => $user->user_login,
            'profile'  => $avatar
        ));
    } else {
        wp_send_json_error(array('message' => 'User not found'));
    }
    wp_die();
}

add_action( 'wp_ajax_check_user', 'commart_better_me_check_user' );
add_action( 'wp_ajax_nopriv_check_user', 'commart_better_me_check_user' );

function commart_better_me_check_user() {
    // دامنه دسترسی: تنها برای کاربرانی که وارد شده‌اند
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'شما به سیستم وارد نشده‌اید.' ) );
    }
    
    global $wpdb;
    
    $username = isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '';
    if ( empty( $username ) ) {
        wp_send_json_error( array( 'message' => 'لطفا نام کاربری را وارد کنید.' ) );
    }
    
    // بررسی وجود کاربر هدف بر اساس نام کاربری
    $user_data = get_user_by( 'login', $username );
    if ( ! $user_data ) {
        wp_send_json_error( array( 'message' => 'کاربری با این نام یافت نشد.' ) );
    }
    
    $current_user_id = get_current_user_id();
    $recipient_id = $user_data->ID;
    
    // جلوگیری از اضافه کردن خود کاربر به عنوان مخاطب
    if ( $current_user_id == $recipient_id ) {
        wp_send_json_error( array( 'message' => 'شما نمی‌توانید خودتان را اضافه کنید.' ) );
    }
    
    // بررسی وجود رکورد چت برای این کاربر و مخاطب
    $table_chat = $wpdb->prefix . 'commart_better_me_chat';
    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_chat WHERE user_id = %d AND bm_recipient_id = %d",
        $current_user_id,
        $recipient_id
    ) );
    
    if ( $exists > 0 ) {
        wp_send_json_error( array( 'message' => 'این کاربر به لیست چت‌های شما اضافه شده است.' ) );
    }
    
    // ثبت مخاطب جدید در جدول چت
    $result = $wpdb->insert(
        $table_chat,
        array(
            'user_id'         => $current_user_id,
            'bm_recipient_id' => $recipient_id,
            // فیلدهای پیام خالی مقداردهی اولیه می‌شوند (تا زمان ارسال اولین پیام)
            'bm_massages_text'=> '',
            'bm_massage_file' => '',
            'bm_massage_image'=> '',
            'bm_massage_voice'=> '',
        ),
        array( '%d', '%d', '%s', '%s', '%s', '%s' )
    );
    
    if ( $result ) {
        $response = array(
            'username' => $user_data->user_login,
            'profile'  => get_avatar_url( $recipient_id ),
            'message'  => 'مخاطب با موفقیت اضافه شد.'
        );
        wp_send_json_success( $response );
    } else {
        wp_send_json_error( array( 'message' => 'خطا در افزودن مخاطب.' ) );
    }
}
?>