<?php
// Existing AJAX actions...

/**
 * Loads Overview content.
 */
add_action('wp_ajax_load_overview', 'load_overview_content');
add_action('wp_ajax_nopriv_load_overview', 'load_overview_content');
function load_overview_content() {
    include plugin_dir_path( __FILE__ ) . '/../overview.php';
    wp_die();
}

/**
 * Loads Today content.
 */
add_action('wp_ajax_load_today', 'load_today_content');
add_action('wp_ajax_nopriv_load_today', 'load_today_content');
function load_today_content() {
    include plugin_dir_path( __FILE__ ) . '/../today.php';
    wp_die();
}

/**
 * Loads My Prize content.
 */
add_action('wp_ajax_load_myprize', 'load_myprize_content');
add_action('wp_ajax_nopriv_load_myprize', 'load_myprize_content');
function load_myprize_content() {
    include plugin_dir_path( __FILE__ ) . '/../myprize.php';
    wp_die();
}


/**
 * Loads Projects content.
 */
add_action('wp_ajax_load_projects', 'load_projects_content');
add_action('wp_ajax_nopriv_load_projects', 'load_projects_content');
function load_projects_content() {
    include plugin_dir_path( __FILE__ ) . '/../projects.php';
    wp_die();
}

/**
 * Loads Employer content.
 */
add_action('wp_ajax_load_employer', 'load_employer_content');
add_action('wp_ajax_nopriv_load_employer', 'load_employer_content');
function load_employer_content() {
    include plugin_dir_path( __FILE__ ) . '/../employer.php';
    wp_die();
}

/**
 * Loads Steps content.
 */
add_action('wp_ajax_load_steps', 'load_steps_content');
add_action('wp_ajax_nopriv_load_steps', 'load_steps_content');
function load_steps_content() {
    include plugin_dir_path( __FILE__ ) . '/../steps.php';
    wp_die();
}

/**
 * Loads Plans content.
 */
add_action('wp_ajax_load_plans', 'load_plans_content');
add_action('wp_ajax_nopriv_load_plans', 'load_plans_content');
function load_plans_content() {
    include plugin_dir_path( __FILE__ ) . '/../plans.php';
    wp_die();
}

/**
 * Loads Targets content.
 */
add_action('wp_ajax_load_targets', 'load_targets_content');
add_action('wp_ajax_nopriv_load_targets', 'load_targets_content');
function load_targets_content() {
    include plugin_dir_path( __FILE__ ) . '/../targets.php';
    wp_die();
}

/**
 * Loads Tasks content.
 */
add_action('wp_ajax_load_tasks', 'load_tasks_content');
add_action('wp_ajax_nopriv_load_tasks', 'load_tasks_content');
function load_tasks_content() {
    include plugin_dir_path( __FILE__ ) . '/../tasks.php';
    wp_die();
}



/**
 * Loads AI Assistant content.
 */
add_action('wp_ajax_load_aissistant', 'load_aissistant_content');
add_action('wp_ajax_nopriv_load_aissistant', 'load_aissistant_content');
function load_aissistant_content() {
    include plugin_dir_path( __FILE__ ) . '/../aissistant.php';
    wp_die();
}

/**
 * Loads File Manager content.
 */
add_action('wp_ajax_load_filemanager', 'load_filemanager_content');
add_action('wp_ajax_nopriv_load_filemanager', 'load_filemanager_content');
function load_filemanager_content() {
    include plugin_dir_path( __FILE__ ) . '/../filemanager.php';
    wp_die();
}

/**
 * Loads Employer Profile content.
 * (This action loads the "Profile as an employer" content)
 */
add_action('wp_ajax_load_employer_profile', 'load_employer_profile_content');
add_action('wp_ajax_nopriv_load_employer_profile', 'load_employer_profile_content');
function load_employer_profile_content() {
    include plugin_dir_path( __FILE__ ) . '/../addemployer.php';
    wp_die();
}

/**
 * Loads Edit Employer Profile content.
 * (This action loads the "Edit Profile" content)
 */
add_action('wp_ajax_load_employer_edit', 'load_employer_edit_content');
add_action('wp_ajax_nopriv_load_employer_edit', 'load_employer_edit_content');
function load_employer_edit_content() {
    include plugin_dir_path( __FILE__ ) . '/../employer-profile.php';
    wp_die();
}

/**
 * Loads Tools content.
 */
add_action('wp_ajax_load_tools', 'load_tools_content');
add_action('wp_ajax_nopriv_load_tools', 'load_tools_content');
function load_tools_content() {
    include plugin_dir_path( __FILE__ ) . '/../tools.php';
    wp_die();
}

/**
 * Handle add or update target.
 */
add_action('wp_ajax_commart_add_update_target', 'commart_add_update_target');
add_action('wp_ajax_nopriv_commart_add_update_target', 'commart_add_update_target');
function commart_add_update_target(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error( 'Missing form data.' );
        wp_die();
    }
    $data_str   = wp_unslash( $_POST['form_data'] );
    parse_str( $data_str, $form_data );
    $table      = $wpdb->prefix . 'commart_better_me_targets';
    $current_user = wp_get_current_user();

    $data = array(
        'user_id'     => $current_user->ID,
        'name'        => sanitize_text_field( $form_data['target_name'] ),
        'description' => sanitize_textarea_field( $form_data['target_description'] ),
        'start_date'  => sanitize_text_field( $form_data['target_start_date'] ),
        'deadline'    => sanitize_text_field( $form_data['target_deadline'] ),
        'type'        => sanitize_text_field( $form_data['target_type'] ),
        'created_at'  => current_time( 'mysql' )
    );
    $target_id = intval( $form_data['target_id'] );

    if ( $target_id ) {
        unset( $data['created_at'] );
        $result = $wpdb->update( $table, $data, array( 'id' => $target_id ) );
        if ( false !== $result ) {
            $response = array(
                'id'          => $target_id,
                'name'        => $data['name'],
                'description' => $data['description'],
                'start_date'  => $data['start_date'],
                'deadline'    => $data['deadline'],
                'type'        => $data['type']
            );
            wp_send_json_success( $response );
        } else {
            wp_send_json_error( 'Failed to update target.' );
        }
    } else {
        $result = $wpdb->insert( $table, $data );
        if ( $result ) {
            $insert_id = $wpdb->insert_id;
            $response = array(
                'id'          => $insert_id,
                'name'        => $data['name'],
                'description' => $data['description'],
                'start_date'  => $data['start_date'],
                'deadline'    => $data['deadline'],
                'type'        => $data['type']
            );
            wp_send_json_success( $response );
        } else {
            wp_send_json_error( 'Failed to add new target.' );
        }
    }
}

/**
 * Handle delete target.
 */
add_action('wp_ajax_commart_delete_target', 'commart_delete_target');
add_action('wp_ajax_nopriv_commart_delete_target', 'commart_delete_target');
function commart_delete_target(){
    global $wpdb;
    if ( empty( $_POST['target_id'] ) ) {
        wp_send_json_error( 'Missing target ID.' );
        wp_die();
    }
    $target_id = intval( $_POST['target_id'] );
    $table     = $wpdb->prefix . 'commart_better_me_targets';
    
    $result = $wpdb->delete( $table, array( 'id' => $target_id ) );
    if ( $result ) {
        wp_send_json_success();
    } else {
        wp_send_json_error( 'Failed to delete target.' );
    }
}

/* ----- Plans AJAX Endpoints ----- */

/**
 * Handle add or update plan.
 */
add_action('wp_ajax_commart_add_update_plan', 'commart_add_update_plan');
add_action('wp_ajax_nopriv_commart_add_update_plan', 'commart_add_update_plan');
function commart_add_update_plan(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error( 'Missing form data.' );
        wp_die();
    }
    $data_str    = wp_unslash( $_POST['form_data'] );
    parse_str( $data_str, $form_data );

    $plans_table   = $wpdb->prefix . 'commart_better_me_plans';
    $targets_table = $wpdb->prefix . 'commart_better_me_targets';
    $current_user  = wp_get_current_user();

    $data = array(
        'target_id'   => intval( $form_data['plan_target_id'] ),
        'user_id'     => $current_user->ID,
        'description' => sanitize_text_field( $form_data['plan_title'] ),
        'budget'      => sanitize_text_field( $form_data['plan_budget'] ),
        'created_at'  => current_time('mysql')
    );
    $plan_id = intval( $form_data['plan_id'] );

    if ( $plan_id ) {
        unset($data['created_at']);
        $result = $wpdb->update( $plans_table, $data, array('id' => $plan_id) );
        if ( false !== $result ) {
            $target = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $targets_table WHERE id = %d", $data['target_id'] ) );
            $response = array(
                'id'          => $plan_id,
                'target_id'   => $data['target_id'],
                'target_name' => $target ? $target->name : '',
                'title'       => $data['description'],
                'budget'      => $data['budget']
            );
            wp_send_json_success( $response );
        } else {
            wp_send_json_error( 'Failed to update plan.' );
        }
    } else {
        $result = $wpdb->insert( $plans_table, $data );
        if ( $result ) {
            $insert_id = $wpdb->insert_id;
            $target = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $targets_table WHERE id = %d", $data['target_id'] ) );
            $response = array(
                'id'          => $insert_id,
                'target_id'   => $data['target_id'],
                'target_name' => $target ? $target->name : '',
                'title'       => $data['description'],
                'budget'      => $data['budget']
            );
            wp_send_json_success( $response );
        } else {
            wp_send_json_error( 'Failed to add new plan.' );
        }
    }
}

/**
 * Handle delete plan.
 */
add_action('wp_ajax_commart_delete_plan', 'commart_delete_plan');
add_action('wp_ajax_nopriv_commart_delete_plan', 'commart_delete_plan');
function commart_delete_plan(){
    global $wpdb;
    if ( empty( $_POST['plan_id'] ) ) {
        wp_send_json_error( 'Missing plan ID.' );
        wp_die();
    }
    $plan_id = intval( $_POST['plan_id'] );
    $plans_table = $wpdb->prefix . 'commart_better_me_plans';
    $result = $wpdb->delete( $plans_table, array('id' => $plan_id) );
    if ( $result ) {
        wp_send_json_success();
    } else {
        wp_send_json_error( 'Failed to delete plan.' );
    }
}

/* ----- Steps AJAX Endpoints ----- */

/**
 * Handle add or update step.
 * Inserts a new step into the table WITHOUT starting the timer.
 */
add_action('wp_ajax_commart_add_update_step', 'commart_add_update_step');
add_action('wp_ajax_nopriv_commart_add_update_step', 'commart_add_update_step');
function commart_add_update_step(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    $data_str = wp_unslash($_POST['form_data']);
    parse_str($data_str, $form_data);

    $steps_table = $wpdb->prefix . 'commart_better_me_steps';
    $plans_table = $wpdb->prefix . 'commart_better_me_plans';
    $current_user = wp_get_current_user();

    // Insert the step with status pending, timer not started (start_date remains NULL),
    // elapsed_time is initially 0, report is empty.
    $data = array(
        'user_id'    => $current_user->ID,
        'plan_id'     => intval($form_data['step_plan_id']),
        'title'       => sanitize_text_field($form_data['step_title']),
        'deadline'    => sanitize_text_field($form_data['step_deadline']),
        'status'      => 'pending',
        'created_at'  => current_time('mysql')
    );
    $step_id = intval($form_data['step_id']);

    if($step_id){
        unset($data['created_at']);
        $result = $wpdb->update($steps_table, $data, array('id' => $step_id));
        if(false !== $result){
            $plan = $wpdb->get_row($wpdb->prepare("SELECT description FROM $plans_table WHERE id = %d", $data['plan_id']));
            $response = array(
                'id'               => $step_id,
                'plan_id'          => $data['plan_id'],
                'plan_title'       => $plan ? $plan->description : '',
                'title'            => $data['title'],
                'deadline'         => $data['deadline'],
                'elapsed'          => 0,
                'elapsed_formatted'=> "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('Failed to update step.');
        }
    } else {
        $result = $wpdb->insert($steps_table, $data);
        if($result){
            $insert_id = $wpdb->insert_id;
            $plan = $wpdb->get_row($wpdb->prepare("SELECT description FROM $plans_table WHERE id = %d", $data['plan_id']));
            $response = array(
                'id'               => $insert_id,
                'plan_id'          => $data['plan_id'],
                'plan_title'       => $plan ? $plan->description : '',
                'title'            => $data['title'],
                'deadline'         => $data['deadline'],
                'elapsed'          => 0,
                'elapsed_formatted'=> "00:00:00"
            );
            wp_send_json_success($response);
        } else {
            wp_send_json_error('Failed to add new step.');
        }
    }
}

/**
 * Handle delete step.
 */
add_action('wp_ajax_commart_delete_step', 'commart_delete_step');
add_action('wp_ajax_nopriv_commart_delete_step', 'commart_delete_step');
function commart_delete_step(){
    global $wpdb;
    if(empty($_POST['step_id'])){
        wp_send_json_error('Missing step ID.');
        wp_die();
    }
    $step_id = intval($_POST['step_id']);
    $steps_table = $wpdb->prefix . 'commart_better_me_steps';
    $result = $wpdb->delete($steps_table, array('id'=>$step_id));
    if($result){
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete step.');
    }
}

/**
 * Handle start step.
 * When the user presses the start button, update the step to set start_date (as current datetime)
 * and change status to 'in_progress'. No timer calculation is done until stop.
 */
add_action('wp_ajax_commart_start_step', 'commart_start_step');
add_action('wp_ajax_nopriv_commart_start_step', 'commart_start_step');


/**
 * Handle stop step.
 * When stop is pressed, calculate elapsed time from the stored start_date until now,
 * update the status to 'completed', clear the start_date (set to NULL),
 * and update the report field with the passed report.
 */
add_action('wp_ajax_commart_stop_step', 'commart_stop_step');
add_action('wp_ajax_nopriv_commart_stop_step', 'commart_stop_step');


/**
 * Handle update step report.
 */
add_action('wp_ajax_commart_update_step_report', 'commart_update_step_report');
add_action('wp_ajax_nopriv_commart_update_step_report', 'commart_update_step_report');
function commart_update_step_report(){
    global $wpdb;
    if(empty($_POST['step_id'])){
        wp_send_json_error('Missing step ID.');
        wp_die();
    }
    if(!isset($_POST['report'])){
        wp_send_json_error('Missing report content.');
        wp_die();
    }
    $step_id = intval($_POST['step_id']);
    $report = sanitize_textarea_field($_POST['report']);
    $steps_table = $wpdb->prefix . 'commart_better_me_steps';
    $result = $wpdb->update($steps_table, array(
        'report' => $report
    ), array('id'=>$step_id));
    if(false !== $result){
        wp_send_json_success(array('report' => $report));
    } else {
        wp_send_json_error('Failed to update report.');
    }
}

/* ----- New AJAX Endpoints for Employer Profile ----- */

/**
 * AJAX handler to check if employer username already exists.
 */
add_action('wp_ajax_commart_check_username', 'commart_check_username');
add_action('wp_ajax_nopriv_commart_check_username', 'commart_check_username');
function commart_check_username(){
    global $wpdb;
    if ( empty($_POST['username']) ) {
        wp_send_json_error('No username provided.');
        wp_die();
    }
    $username = sanitize_text_field($_POST['username']);
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE employer_username = %s", $username));
    if ( $exists > 0 ) {
        wp_send_json_error('Username already exists.');
    } else {
        wp_send_json_success('Username is available.');
    }
    wp_die();
}

/**
 * AJAX handler for adding an employer profile.
 * Stores the employer information (entered in the addemployer.php form) into the 
 * employer_profiles table with the current user's ID.
 */
add_action('wp_ajax_commart_add_employer', 'commart_add_employer');
add_action('wp_ajax_nopriv_commart_add_employer', 'commart_add_employer');
function commart_add_employer(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    $data_str = wp_unslash($_POST['form_data']);
    parse_str($data_str, $form_data);

    // Validate required fields.
    $required_fields = ['employer_username', 'employer_name', 'company_name', 'activity_field', 'business_mobile', 'email'];
    foreach ( $required_fields as $field ) {
        if ( empty($form_data[$field]) ) {
            wp_send_json_error("Missing field: {$field}");
            wp_die();
        }
    }

    // Sanitize form data.
    $employer_username = sanitize_text_field($form_data['employer_username']);
    $employer_name     = sanitize_text_field($form_data['employer_name']);
    $email             = sanitize_email($form_data['email']);
    $company_name      = sanitize_text_field($form_data['company_name']);
    $activity_field    = sanitize_text_field($form_data['activity_field']);
    $brands            = isset($form_data['brands']) ? sanitize_text_field($form_data['brands']) : '';
    $business_mobile   = sanitize_text_field($form_data['business_mobile']);
    $site              = isset($form_data['site']) ? esc_url_raw($form_data['site']) : '';

    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $data = [
        'employer_username' => $employer_username,
        'employer_name'     => $employer_name,
        'email'             => $email,
        'company_name'      => $company_name,
        'activity_field'    => $activity_field,
        'brands'            => $brands,
        'business_mobile'   => $business_mobile,
        'site'              => $site,
        'user_id'           => $user_id,
        'created_by'        => $current_user->ID,
    ];

    $inserted = $wpdb->insert($table, $data);
    if ( $inserted ) {
        wp_send_json_success('Employer added successfully!');
    } else {
        wp_send_json_error('Failed to add employer.');
    }
    wp_die();
}

/**
 * AJAX handler for listing employer profiles for the current user.
 */
add_action('wp_ajax_commart_list_employers', 'commart_list_employers');
add_action('wp_ajax_nopriv_commart_list_employers', 'commart_list_employers');
function commart_list_employers(){
    global $wpdb;
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $current_user = wp_get_current_user();
    $results = $wpdb->get_results( 
        $wpdb->prepare("SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC", $current_user->ID), 
        ARRAY_A
    );
    if($results){
        wp_send_json_success($results);
    } else {
        wp_send_json_error('No records found.');
    }
    wp_die();
}

/**
 * AJAX handler for deleting an employer profile.
 */
add_action('wp_ajax_commart_delete_employer', 'commart_delete_employer');
add_action('wp_ajax_nopriv_commart_delete_employer', 'commart_delete_employer');
function commart_delete_employer(){
    global $wpdb;
    if ( empty($_POST['employer_id']) ) {
        wp_send_json_error('Missing employer id.');
        wp_die();
    }
    $id = intval($_POST['employer_id']);
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $deleted = $wpdb->delete($table, array('id' => $id));
    if($deleted){
        wp_send_json_success('Employer deleted successfully!');
    } else {
        wp_send_json_error('Failed to delete employer.');
    }
    wp_die();
}

/**
 * AJAX handler for retrieving a single employer profile.
 */
add_action('wp_ajax_commart_get_employer', 'commart_get_employer');
add_action('wp_ajax_nopriv_commart_get_employer', 'commart_get_employer');
function commart_get_employer(){
    global $wpdb;
    if ( empty($_POST['employer_id']) ) {
        wp_send_json_error('Missing employer id.');
        wp_die();
    }
    $id = intval($_POST['employer_id']);
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    if($record){
        wp_send_json_success($record);
    } else {
        wp_send_json_error('Record not found.');
    }
    wp_die();
}

/**
 * AJAX handler for updating an employer profile.
 * Ensures that if the employer changes their details in the "My Employer" section,
 * the record for that user is updated.
 */
add_action('wp_ajax_commart_update_employer', 'commart_update_employer');
add_action('wp_ajax_nopriv_commart_update_employer', 'commart_update_employer');
function commart_update_employer(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    $data_str = wp_unslash($_POST['form_data']);
    parse_str($data_str, $form_data);
    
    if ( empty($form_data['record_id']) ) {
        wp_send_json_error('Missing record id.');
        wp_die();
    }
    
    $id = intval($form_data['record_id']);
    // Validate required fields.
    $required_fields = ['employer_username', 'employer_name', 'company_name', 'activity_field', 'business_mobile', 'email'];
    foreach ( $required_fields as $field ) {
        if ( empty($form_data[$field]) ) {
            wp_send_json_error("Missing field: {$field}");
            wp_die();
        }
    }
    
    // Sanitize form data.
    $data = [
        'employer_username' => sanitize_text_field($form_data['employer_username']),
        'employer_name'     => sanitize_text_field($form_data['employer_name']),
        'email'             => sanitize_email($form_data['email']),
        'company_name'      => sanitize_text_field($form_data['company_name']),
        'activity_field'    => sanitize_text_field($form_data['activity_field']),
        'brands'            => isset($form_data['brands']) ? sanitize_text_field($form_data['brands']) : '',
        'business_mobile'   => sanitize_text_field($form_data['business_mobile']),
        'site'              => isset($form_data['site']) ? esc_url_raw($form_data['site']) : '',
    ];
    
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $updated = $wpdb->update($table, $data, array('id' => $id));
    if($updated !== false){
        wp_send_json_success('Employer updated successfully!');
    } else {
        wp_send_json_error('Failed to update employer.');
    }
    wp_die();
}
add_action('wp_ajax_commart_set_my_employer', 'commart_set_my_employer');
function commart_set_my_employer(){
    global $wpdb;
    if ( empty($_POST['form_data']) ) {
        wp_send_json_error('Missing form data.');
        wp_die();
    }
    parse_str( wp_unslash($_POST['form_data']), $form_data );
    
    if ( empty($form_data['employer_username']) ) {
        wp_send_json_error('Missing employer username.');
        wp_die();
    }
    
    $employer_username = sanitize_text_field($form_data['employer_username']);
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    // Search for the employer profile by employer_username.
    $employer = $wpdb->get_row(
        $wpdb->prepare("SELECT id FROM $table WHERE employer_username = %s", $employer_username),
        ARRAY_A
    );
    
    if ( !$employer ) {
        wp_send_json_error('Employer not found.');
        wp_die();
    }
    
    // Save the employer id in the current user's meta.
    $current_user = wp_get_current_user();
    update_user_meta( $current_user->ID, 'commart_my_employer_id', $employer['id'] );
    
    wp_send_json_success('My employer has been set successfully!');
    wp_die();
}

/**
 * AJAX endpoint for retrieving "My Employer" for the current user.
 * This endpoint reads the employer id stored in the user's meta (with key "commart_my_employer_id")
 * and returns the employer's profile details.
 */
add_action('wp_ajax_commart_get_my_employer', 'commart_get_my_employer_custom');
function commart_get_my_employer_custom(){
    global $wpdb;
    $current_user = wp_get_current_user();
    $employer_id = get_user_meta( $current_user->ID, 'commart_my_employer_id', true );
    
    if ( empty( $employer_id ) ) {
        wp_send_json_error('No employer set.');
        wp_die();
    }
    
    $table = $wpdb->prefix . 'commart_better_me_employer_profiles';
    $record = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($employer_id)),
        ARRAY_A
    );
    
    if ( $record ) {
        wp_send_json_success($record);
    } else {
        wp_send_json_error('Employer record not found.');
    }
    
    wp_die();
}


?>