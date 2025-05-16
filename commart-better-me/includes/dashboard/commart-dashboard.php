<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function my_plugin_enqueue_styles() {
    wp_enqueue_style( 'dashboard-style', plugin_dir_url( __FILE__ ) . 'includes/css/dashboard.css' );
}
add_action( 'wp_enqueue_scripts', 'my_plugin_enqueue_styles' );


// Start output buffering to prevent "headers already sent" errors.
ob_start();

// Set caching headers based on the action parameter.
$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

if ( $action === 'load_filemanager' ) {
    // For file manager requests (i.e. file loading), prevent caching.
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
} 

// Build an absolute path for the loading.php file.
$loading_file_path = plugin_dir_path( __FILE__ ) . 'loading.php';

if ( file_exists( $loading_file_path ) ) {
    include( $loading_file_path );
} else {
    error_log( "Loading file not found: " . $loading_file_path );
    echo '<!-- Loading file not found: ' . esc_html( $loading_file_path ) . ' -->';
}

$current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Commart Better Me', 'commart-better-me' ); ?></title>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(window).on('load', function(){
            setTimeout(function(){
                $("#loading-better-me").fadeOut("slow");
            }, 2000);
        });
        
        $(document).ready(function(){
            // Function to load content via AJAX.
            function loadContent(path) {
                $('.better-me-main-content .dashboard-body-main-content').html('<p>Loading...</p>');
                $.ajax({
                    url: path,
                    type: 'GET',
                    success: function(response){
                        $('.better-me-main-content .dashboard-body-main-content').html(response);
                    },
                    error: function(){
                        $('.better-me-main-content .dashboard-body-main-content').html('<p>Error loading content.</p>');
                    }
                });
            }
            
            // Ensure ajaxurl is defined.
            if (typeof ajaxurl === 'undefined') {
                var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            }
            
            // Sidebar toggle: hide sidebar for Chat, AI Assistant, and File Manager; otherwise, show it.
            function toggleSidebar(option) {
                if (option === "Chat" || option === "AI Assistant" || option === "File Manager") {
                    $('.dashboard-body-sidebar').hide();
                    $('.dashboard-body').css('grid-template-columns', 'minmax(min-content, 175px) 1fr');
                } else {
                    $('.dashboard-body-sidebar').show();
                    $('.dashboard-body').css('grid-template-columns', 'minmax(min-content, 175px) minmax(max-content, 1fr) minmax(min-content, 1px)');
                }
            }
            
            // Hide all tab containers.
            function hideAllTabs() {
                $('#dashboard-tabs, #effort-tabs, #progress-tabs, #employers-tabs, #tools-tabs').hide();
            }
            
            // Main navigation click handler.
            $('.navigation a').on('click', function(e){
                e.preventDefault();
                
                // Remove active class from all main nav links.
                $('.navigation a').removeClass('active');
                $(this).addClass('active');
                
                // Get the option text from the clicked link.
                var option = $(this).find('span').text().trim();
                hideAllTabs();
                toggleSidebar(option);
                
                // Determine which tab container and default content to load based on option.
                if(option === "Dashboard"){
                    // Show Dashboard tabs container and mark Overview as active.
                    $('#dashboard-tabs').show();
                    $('#dashboard-tabs a').removeClass('active');
                    $('#dashboard-tabs a').each(function(){
                        if($(this).text().trim() === "Overview"){
                            $(this).addClass('active');
                        }
                    });
                    loadContent(ajaxurl + '?action=load_overview');
                } else if(option === "Progress"){
                    $('#progress-tabs').show();
                    $('#progress-tabs a').removeClass('active');
                    $('#progress-tabs a').each(function(){
                        if($(this).text().trim() === "Steps"){
                            $(this).addClass('active');
                        }
                    });
                    loadContent(ajaxurl + '?action=load_steps');
                } else if(option === "Effort"){
                    $('#effort-tabs').show();
                    $('#effort-tabs a').removeClass('active');
                    $('#effort-tabs a').each(function(){
                        if($(this).text().trim() === "Tasks"){
                            $(this).addClass('active');
                        }
                    });
                    loadContent(ajaxurl + '?action=load_tasks');
                } else if(option === "Chat"){
                    loadContent(ajaxurl + '?action=load_chat');
                } else if(option === "AI Assistant"){
                    loadContent(ajaxurl + '?action=load_aissistant');
                } else if(option === "File Manager"){
                    loadContent(ajaxurl + '?action=load_filemanager');
                } else if(option === "Employers"){
                    // Show Employers tabs container and mark "Profile as an employer" as active.
                    $('#employers-tabs').show();
                    $('#employers-tabs a').removeClass('active');
                    $('#employers-tabs a').each(function(){
                        if($(this).text().trim() === "Profile as an employer"){
                            $(this).addClass('active');
                        }
                    });
                    loadContent(ajaxurl + '?action=load_employer_profile');
                } else if(option === "Tools"){
                    $('#tools-tabs').show();
                    $('#tools-tabs a').removeClass('active');
                    $('#tools-tabs a').each(function(){
                        // در صورت وجود چند گزینه در تب‌ها، می‌توانید گزینه پیش‌فرض را انتخاب کنید.
                        if($(this).text().trim() === "Tools"){
                            $(this).addClass('active');
                        }
                    });
                    loadContent(ajaxurl + '?action=load_tools');
                } else {
                    // Fallback option: load Overview.
                    loadContent(ajaxurl + '?action=load_overview');
                }
            });
            
            // Tab click handler for the header navigation tab containers.
            $('.tabs a').on('click', function(e){
                e.preventDefault();
                // Remove active class from all tabs within the same container.
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
                var action = $(this).data('action');
                if(action) {
                    loadContent(ajaxurl + '?action=' + action);
                }
            });
            
            // On initial page load, default to Dashboard (Overview tab).
            var defaultFile = ajaxurl + '?action=load_overview';
            $('.navigation a').removeClass('active');
            $('.navigation a').each(function(){
                if($(this).find('span').text().trim() === "Dashboard"){
                    $(this).addClass('active');
                }
            });
            hideAllTabs();
            $('#dashboard-tabs').show();
            $('#dashboard-tabs a').each(function(){
                if($(this).text().trim() === "Overview"){
                    $(this).addClass('active');
                }
            });
            loadContent(defaultFile);
        });
    </script>
    <style>
        #loading-better-me {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: black;
            z-index: 9999;
        }
        .navigation a.active, .tabs a.active {
            font-weight: bold;
        }
        .dashboard-header-navigation .tabs, 
        .dashboard-header-navigation .custom-title {
            display: none;
        }
        /* Style adjustments for tab containers (customize as needed) */
        #dashboard-tabs, #effort-tabs, #progress-tabs, #employers-tabs, #tools-tabs {
            display: none;
            margin: 10px 0;
        }
        #dashboard-tabs a, #effort-tabs a, #progress-tabs a, #employers-tabs a, #tools-tabs a {
            margin-right: 15px;
            cursor: pointer;
        }
    </style>
</head>
<body class="better-me-container">
    <div class="better-me-dashboard">
        <header class="dashboard-header">
            <div class="dashboard-header-logo">
                <div class="better-me-logo">
                    <span class="better-me-logo-icon">
                        <img src="https://assets.codepen.io/285131/almeria-logo.svg" alt="Logo" />
                    </span>
                    <h1 class="better-me-logo-title">
                        <span>Commart</span>
                        <span>Better me</span>
                    </h1>
                </div>
            </div>
            <div class="dashboard-header-navigation">
                <!-- Tab containers for Dashboard, Effort, Progress, Employers, and Tools -->
                <div id="dashboard-tabs" class="tabs" style="display: flex;">
                    <a href="#" data-action="load_overview">Overview</a>
                    <a href="#" data-action="load_today">Today</a>
                    <a href="#" data-action="load_myprize">My prize</a>
                </div>
                <div id="effort-tabs" class="tabs" style="display: flex;">
                    <a href="#" data-action="load_tasks">Tasks</a>
                    <a href="#" data-action="load_projects">Projects</a>
                    <a href="#" data-action="load_employer">My Employer</a>
                </div>
                <div id="progress-tabs" class="tabs" style="display: flex;">
                    <a href="#" data-action="load_steps">Steps</a>
                    <a href="#" data-action="load_plans">Plans</a>
                    <a href="#" data-action="load_targets">Targets</a>
                </div>
               <div id="employers-tabs" class="tabs" style="display: flex;">
    <!-- تب "Profile as an employer" -->
    <a href="#" data-action="load_employer_profile">Profile as an employer</a>
    <!-- تب "Edit Profile" -->
    <a href="#" data-action="load_employer_edit">Edit Profile</a>
</div>

<script>
    $(document).ready(function(){
        function loadContent(path) {
            $('.better-me-main-content .dashboard-body-main-content').html('<p>Loading...</p>');
            $.ajax({
                url: path,
                type: 'GET',
                success: function(response){
                    $('.better-me-main-content .dashboard-body-main-content').html(response);
                },
                error: function(){
                    $('.better-me-main-content .dashboard-body-main-content').html('<p>Error loading content.</p>');
                }
            });
        }
        
        $('.navigation a, .tabs a').on('click', function(e){
            e.preventDefault();
            $('.navigation a, .tabs a').removeClass('active');
            $(this).addClass('active');

            var action = $(this).data("action");
            if(action) {
                loadContent(ajaxurl + '?action=' + action);
            } else {
                loadContent(ajaxurl + '?action=load_overview');
            }
        });

        // بارگذاری اولیه، تب Overview فعال می‌شود.
        loadContent(ajaxurl + '?action=load_overview');
    });
</script>
                <div id="tools-tabs" class="tabs" style="display: flex;">
                    <!-- در صورت نیاز می‌توانید چند گزینه ابزار اضافه کنید -->
                    <a href="#" data-action="load_tools">Tools</a>
                </div>
                <div class="custom-title"></div>
            </div>
            <div class="dashboard-header-actions">
                <button class="user-profile">
                    <span><?php echo esc_html( $current_user->display_name ); ?></span>
                    <span>
                        <?php echo get_avatar( $current_user->ID, 48 ); ?>
                    </span>
                </button>
            </div>
        </header>
        <div class="dashboard-body">
            <div class="dashboard-body-navigation">
                <nav class="navigation">
                    <a href="#" data-action="load_overview">
                        <i class="ph-browsers"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" data-action="load_steps">
                        <i class="ph-check-square"></i>
                        <span>Progress</span>
                    </a>
                    <a href="#" data-action="load_tasks">
                        <i class="ph-swap"></i>
                        <span>Effort</span>
                    </a>
                    <a href="#" data-action="load_chat">
                        <i class="ph-file-text"></i>
                        <span>Chat</span>
                    </a>
                    <a href="#" data-action="load_aissistant">
                        <i class="ph-globe"></i>
                        <span>AI Assistant</span>
                    </a>
                    <a href="#" data-action="load_filemanager">
                        <i class="ph-folder"></i>
                        <span>File Manager</span>
                    </a>
                    <a href="#" data-action="load_employers">
                        <i class="ph-user"></i>
                        <span>Employers</span>
                    </a>
                    <a href="#" data-action="load_tools">
                        <i class="ph-tool"></i>
                        <span>Tools</span>
                    </a>
                </nav>
            </div>
            <div class="better-me-main-content">
                <div class="dashboard-body-main-content"></div>
            </div>
            
        </div>
    </div>
</body>
</html>