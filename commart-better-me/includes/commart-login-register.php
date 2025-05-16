<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


function commart_initialize_user_records( $user_id ) {
	global $wpdb;
	
	// Table names using WordPress prefix.
	$employers_table        = $wpdb->prefix . 'better_me_effort_employers';
	$brands_table           = $wpdb->prefix . 'better_me_brands';
	$projects_table         = $wpdb->prefix . 'better_me_effort_projects';
	$tasks_table            = $wpdb->prefix . 'better_me_effort_tasks';
	$targets_table          = $wpdb->prefix . 'better_me_progress_targets';
	$plans_table            = $wpdb->prefix . 'better_me_progress_plans';
	$steps_table            = $wpdb->prefix . 'better_me_progress_steps';
	$chats_table            = $wpdb->prefix . 'better_me_chats';
	$chat_messages_table    = $wpdb->prefix . 'better_me_chat_messages';
	$chat_participants_table= $wpdb->prefix . 'better_me_chat_participants';
	$file_manager_table     = $wpdb->prefix . 'better_me_file_manager';
	// Mobile transfer table is not initialized by default.
	
	// 1. Employers: Create default employer record if none exists.
	$employer_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $employers_table WHERE user_id = %d", $user_id ) );
	if ( $employer_exists == 0 ) {
		$wpdb->insert( 
			$employers_table, 
			array(
				'user_id'        => $user_id,
				'employer_name'  => 'Default Employer',
				'company_name'   => 'Default Company',
				'brands'         => json_encode( array() ),
				'collaboration_start' => current_time( 'Y-m-d' ),
			)
		);
	}
	
	// 2. Brands: Create default brand if none exists.
	$brand_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $brands_table WHERE user_id = %d", $user_id ) );
	if ( $brand_exists == 0 ) {
		$wpdb->insert( 
			$brands_table, 
			array(
				'user_id'         => $user_id,
				'brand_name'      => 'Default Brand',
				'brand_description'=> 'Automatically created default brand.',
			)
		);
		$default_brand_id = $wpdb->insert_id;
	} else {
		$default_brand_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $brands_table WHERE user_id = %d LIMIT 1", $user_id ) );
	}
	
	// 3. Projects: Create default project if none exists.
	$project_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $projects_table WHERE user_id = %d", $user_id ) );
	if ( $project_exists == 0 ) {
		$wpdb->insert( 
			$projects_table, 
			array(
				'user_id'           => $user_id,
				'project_title'     => 'Default Project',
				'brand_id'          => $default_brand_id,
				'project_description'=> 'Automatically created default project.',
				'deadline'          => date( 'Y-m-d', strtotime( '+30 days' ) ),
				'project_amount'    => 0,
			)
		);
		$default_project_id = $wpdb->insert_id;
	} else {
		$default_project_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $projects_table WHERE user_id = %d LIMIT 1", $user_id ) );
	}
	
	// 4. Tasks: Create default task if none exists.
	$task_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $tasks_table WHERE user_id = %d", $user_id ) );
	if ( $task_exists == 0 ) {
		$wpdb->insert( 
			$tasks_table, 
			array(
				'user_id'      => $user_id,
				'task_title'   => 'Default Task',
				'brand_id'     => $default_brand_id,
				'project_id'   => $default_project_id,
				'deadline'     => date( 'Y-m-d', strtotime( '+15 days' ) ),
				'task_amount'  => 0,
			)
		);
	}
	
	// 5. Targets: Create default target if none exists.
	$target_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $targets_table WHERE user_id = %d", $user_id ) );
	if ( $target_exists == 0 ) {
		$wpdb->insert( 
			$targets_table, 
			array(
				'user_id'            => $user_id,
				'target_short_name'  => 'Default Target',
				'target_description' => 'Automatically created target upon registration/login.',
				'start_date'         => current_time( 'Y-m-d' ),
				'end_date'           => null,
				'target_type'        => 'کوتاه مدت'
			)
		);
		$default_target_id = $wpdb->insert_id;
	} else {
		$default_target_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $targets_table WHERE user_id = %d LIMIT 1", $user_id ) );
	}
	
	// 6. Plans: Create default plan for the default target if none exists.
	$plan_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $plans_table WHERE user_id = %d", $user_id ) );
	if ( $plan_exists == 0 ) {
		$wpdb->insert( 
			$plans_table, 
			array(
				'user_id'           => $user_id,
				'target_id'         => $default_target_id,
				'plan_description'  => 'Default plan for your target.',
				'estimated_budget'  => 0,
			)
		);
		$default_plan_id = $wpdb->insert_id;
	} else {
		$default_plan_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $plans_table WHERE user_id = %d LIMIT 1", $user_id ) );
	}
	
	// 7. Steps: Create default step for the default plan if none exists.
	$step_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $steps_table WHERE plan_id = %d", $default_plan_id ) );
	if ( $step_exists == 0 ) {
		$wpdb->insert( 
			$steps_table, 
			array(
				'plan_id'          => $default_plan_id,
				'step_description' => 'Default step in your plan.',
				'start_date'       => current_time( 'Y-m-d' ),
				'deadline'         => date( 'Y-m-d', strtotime( '+7 days' ) ),
			)
		);
	}
	
	// 8. Private Chat: Create welcome private chat if not exists.
	$private_chat_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $chats_table WHERE (user_x = %d OR user_y = %d) AND chat_type = 'خصوصی'", $user_id, $user_id ) );
	if ( $private_chat_exists == 0 ) {
		$welcome_message = array(
			array(
				'sender'  => 0,
				'message' => 'Welcome to Better Me! Start your journey here.',
				'sent_at' => current_time( 'mysql' )
			)
		);
		$wpdb->insert( 
			$chats_table, 
			array(
				'conv_id'  => 'private-welcome-' . $user_id,
				'chat_type'=> 'خصوصی',
				'user_x'   => $user_id,
				'user_y'   => 0,
				'messages' => wp_json_encode( $welcome_message )
			)
		);
	}
	
	// 9. Group Chat: Create a default group chat if not already created,
	// and add the current user as a participant.
	$group_chat = $wpdb->get_row( "SELECT * FROM $chats_table WHERE conv_id = 'group-default'" );
	if ( ! $group_chat ) {
		// Create the default group chat.
		$wpdb->insert( 
			$chats_table, 
			array(
				'conv_id'   => 'group-default',
				'chat_type' => 'گروهی',
				'project_id'=> null,   // Can be linked to a project if needed.
				'max_storage' => 200,
			)
		);
		$group_chat_id = $wpdb->insert_id;
	} else {
		$group_chat_id = $group_chat->id;
	}
	// Add current user as participant if not already present.
	$participant_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $chat_participants_table WHERE chat_id = %d AND user_id = %d", $group_chat_id, $user_id ) );
	if ( $participant_exists == 0 ) {
		$wpdb->insert( 
			$chat_participants_table, 
			array(
				'chat_id' => $group_chat_id,
				'user_id' => $user_id,
				'joined_at' => current_time( 'mysql' )
			)
		);
	}
	
	// 10. File Manager: Set default quota if no file record exists.
	$file_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $file_manager_table WHERE user_id = %d", $user_id ) );
	if ( $file_exists == 0 ) {
		// Here we choose to store the default file manager quota as a user meta value.
		update_user_meta( $user_id, 'commart_file_manager_quota', 500 ); // 500 MB default.
	}
}

if ( is_user_logged_in() ) {
	$current_user_id = get_current_user_id();
	commart_initialize_user_records( $current_user_id );
}

do_action( 'woocommerce_before_customer_login_form' );
?>

<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
<?php endif; ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        /* CSS unchanged */
        *, *::after, *::before {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            user-select: none;
        }
        .commart-login-register {
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            background-color: #ecf0f3;
            color: #a0a5a8;
        }
        .commart-login-register-main {
            position: relative;
            width: 1000px;
            min-width: 1000px;
            min-height: 600px;
            height: 600px;
            padding: 25px;
            border-radius: 12px;
            overflow: hidden;
        }
        @media(max-width: 1200px) {
            .commart-login-register-main { transform: scale(.7); }
        }
        @media(max-width: 1000px) {
            .commart-login-register-main { transform: scale(.6); }
        }
        @media(max-width: 800px) {
            .commart-login-register-main { transform: scale(.5); }
        }
        @media(max-width: 600px) {
            .commart-login-register-main { transform: scale(.4); }
        }
        .commart-login-register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            top: 0;
            width: 600px;
            height: 100%;
            padding: 25px;
            background-color: #2b2b2b;
            transition: 1.25s;
        }
        .commart-login-register-form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            width: 100%;
            height: 100%;
        }
        .commart-login-register-form__icon {
            object-fit: contain;
            width: 30px;
            margin: 0 5px;
            opacity: 0.5;
            transition: 0.15s;
        }
        .commart-login-register-form__icon:hover {
            opacity: 1;
            transition: 0.15s;
            cursor: pointer;
        }
        .commart-login-register-form__input {
            width: 350px;
            height: 40px;
            margin: 4px 0;
            padding-left: 25px;
            font-size: 13px;
            letter-spacing: 0.15px;
            border: none;
            outline: none;
            font-family: 'Montserrat', sans-serif;
            background-color: #ecf0f3;
            transition: 0.25s ease;
            border-radius: 8px;
        }
        .commart-login-register-form__span {
            margin-top: 30px;
            margin-bottom: 12px;
        }
        .commart-login-register-form__link {
            color: #181818;
            font-size: 15px;
            margin-top: 25px;
            border-bottom: 1px solid #a0a5a8;
            line-height: 2;
        }
        .commart-login-register-title {
            font-size: 34px;
            font-weight: 700;
            line-height: 3;
            color: #ffffff;
        }
        .commart-login-register-description {
            font-size: 14px;
            letter-spacing: 0.25px;
            text-align: center;
            line-height: 1.6;
        }
        .commart-login-register-button {
            width: 180px;
            height: 50px;
            border-radius: 25px;
            margin-top: 50px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1.15px;
            background-color: #4B70E2;
            color: #f9f9f9;
            border: none;
            outline: none;
        }
        .commart-login-register-a-container {
            z-index: 100;
            left: calc(100% - 600px);
        }
        .commart-login-register-b-container {
            left: calc(100% - 600px);
            z-index: 0;
        }
        .commart-login-register-switch {
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 400px;
            padding: 50px;
            z-index: 200;
            transition: 1.25s;
            background-color: #383939;
            overflow: hidden;
        }
        .commart-login-register-switch__container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            position: absolute;
            width: 400px;
            padding: 50px 55px;
            transition: 1.25s;
        }
        .commart-login-register-switch__button {
            cursor: pointer;
        }
        .commart-login-register-switch__button:hover {
            transform: scale(0.985);
            transition: 0.25s;
        }
        .commart-login-register-switch__button:active,
        .commart-login-register-switch__button:focus {
            transform: scale(0.97);
            transition: 0.25s;
        }
        .is-txr {
            left: calc(100% - 400px);
            transition: 1.25s;
            transform-origin: left;
        }
        .is-txl {
            left: 0;
            transition: 1.25s;
            transform-origin: right;
        }
        .is-z200 {
            z-index: 200;
            transition: 1.25s;
        }
        .is-hidden {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            transition: 1.25s;
        }
        .is-gx {
            animation: is-gx 1.25s;
        }
        @keyframes is-gx {
            0%, 10%, 100% { width: 400px; }
            30%, 50% { width: 500px; }
        }
    </style>
    <title><?php bloginfo( 'name' ); ?> - Login/Register</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('commart-login-register'); ?>>
    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
    <div class="commart-login-register-main">
        <!-- فرم ثبت نام -->
        <div class="commart-login-register-container commart-login-register-a-container" id="commart-login-register-a-container">
            <form id="commart-login-register-a-form" method="post" class="commart-login-register-form woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
			<?php do_action( 'woocommerce_register_form_start' ); ?>
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input type="text" class="commart-login-register-form__input woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
				</p>
			<?php endif; ?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="email" class="commart-login-register-form__input woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" />
			</p>
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input type="password" class="commart-login-register-form__input woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
			<?php endif; ?>
			<?php do_action( 'woocommerce_register_form' ); ?>
			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="commart-login-register-button woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
			</p>
			<?php do_action( 'woocommerce_register_form_end' ); ?>
            </form>
        </div>
        <!-- فرم ورود -->
        <div class="commart-login-register-container commart-login-register-b-container" id="commart-login-register-b-container">
           <form id="commart-login-register-b-form" class="commart-login-register-form woocommerce-form woocommerce-form-login login" method="post" novalidate>
			<?php do_action( 'woocommerce_login_form_start' ); ?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
			</p>
			<?php do_action( 'woocommerce_login_form' ); ?>
			<p class="form-row">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
				</label>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="commart-login-register-button woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
			</p>
			<p class="woocommerce-LostPassword lost_password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
			</p>
			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
        </div>
        <!-- قسمت تعویض فرم -->
        <div class="commart-login-register-switch" id="commart-login-register-switch-cnt">
            <div class="commart-login-register-switch__circle"></div>
            <div class="commart-login-register-switch__circle commart-login-register-switch__circle--t"></div>
            <div class="commart-login-register-switch__container" id="commart-login-register-switch-c1">
                <h2 class="commart-login-register-switch__title commart-login-register-title">Welcome Back!</h2>
                <p class="commart-login-register-switch__description commart-login-register-description">To keep connected with us please login with your personal info</p>
                <button class="commart-login-register-switch__button commart-login-register-button switch-btn">SIGN IN</button>
            </div>
            <div class="commart-login-register-switch__container is-hidden" id="commart-login-register-switch-c2">
                <h2 class="commart-login-register-switch__title commart-login-register-title">Hello Friend!</h2>
                <p class="commart-login-register-switch__description commart-login-register-description">Enter your personal details and start journey with us</p>
                <button class="commart-login-register-switch__button commart-login-register-button switch-btn">SIGN UP</button>
            </div>
        </div>
    </div>
    <script>
        // بروزرسانی فیلد نام کاربری قبل از ارسال فرم ثبت نام
        document.getElementById('commart-login-register-a-form').addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="user_email"]').value;
            // استخراج قسمت قبل از @ برای استفاده به عنوان نام کاربری
            this.querySelector('#user_login').value = email.split('@')[0];
        });
        // JavaScript برای تغییر بین فرم‌ها با استفاده از سلکتورهای به‌روز شده
        let switchCtn = document.querySelector("#commart-login-register-switch-cnt");
        let switchC1 = document.querySelector("#commart-login-register-switch-c1");
        let switchC2 = document.querySelector("#commart-login-register-switch-c2");
        let switchCircles = document.querySelectorAll(".commart-login-register-switch__circle");
        let switchBtns = document.querySelectorAll(".switch-btn");
        let aContainer = document.querySelector("#commart-login-register-a-container");
        let bContainer = document.querySelector("#commart-login-register-b-container");
        let changeForm = (e) => {
            switchCtn.classList.add("is-gx");
            setTimeout(function(){
                switchCtn.classList.remove("is-gx");
            }, 1500);
            switchCtn.classList.toggle("is-txr");
            switchCircles[0].classList.toggle("is-txr");
            switchCircles[1].classList.toggle("is-txr");
            switchC1.classList.toggle("is-hidden");
            switchC2.classList.toggle("is-hidden");
            aContainer.classList.toggle("is-txl");
            bContainer.classList.toggle("is-txl");
            bContainer.classList.toggle("is-z200");
        };
        let mainF = () => {
            for (let i = 0; i < switchBtns.length; i++) {
                switchBtns[i].addEventListener("click", changeForm);
            }
        };
        window.addEventListener("load", mainF);
    </script>
    <?php wp_footer(); ?>
</body>
</html>
<?php endif; ?>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>