<?php
/**
 * فایل license.php (وضعیت جدید فرمول لایسنس)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // جلوگیری از دسترسی مستقیم
}

/**
 * تبدیل رشته حروف به رشته اعداد با استفاده از نگاشت a=1, b=2, ...
 */
function convertLettersToNumbers( $str ) {
    $result = '';
    $str = strtolower( $str );
    $length = strlen( $str );
    for ( $i = 0; $i < $length; $i++ ) {
        $char = $str[$i];
        if ( ctype_alpha( $char ) ) {
            // نگاشت a=1, b=2, ... z=26.
            $result .= ( ord( $char ) - 96 );
        }
    }
    return $result;
}

/**
 * تولید رشته‌ای از حروف انگلیسی تصادفی با تعداد بین 2 تا 4 حرف
 */
function getRandomLetters( $min = 2, $max = 4 ) {
    $letters = '';
    $count = rand( $min, $max );
    $possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for ( $i = 0; $i < $count; $i++ ) {
        $letters .= $possible[rand( 0, strlen( $possible ) - 1 )];
    }
    return $letters;
}

// مسیر فایل لاگ (در پوشه admin پلاگین)
$log_file = plugin_dir_path( __FILE__ ) . 'license.log';

// دریافت کلید لایسنس قبلی از دیتابیس
$saved_license_key = get_option( 'plugin_example_saved_license_key', '' );

// دریافت کلید لایسنس ذخیره شده برای راکر از دیتابیس
$rocker_license_key = get_option( 'rocker_status_license_key', '' );

// استخراج بخش کامل URL سایت
$site_url = site_url();
$host_full = parse_url( $site_url, PHP_URL_HOST );
if ( strpos( $host_full, 'www.' ) === 0 ) {
    $host_full = substr( $host_full, 4 );
}

// استخراج قسمت اصلی از دامنه برای محاسبه لایسنس (مثلاً "amprize")
$host_parts = explode( '.', $host_full );
$expected_license_alpha = $host_parts[0];

// تبدیل به عدد (base)
$base = convertLettersToNumbers( $expected_license_alpha );

// در این حالت، فرمول لایسنس به صورت زیر تغییر کرده:
// (حروف و اعداد رندوم) - (عدد اصلی یا base) - (حروف و اعداد رندوم)
// یعنی فقط قسمت وسط (base) مورد استفاده قرار می‌گیرد و بخش‌های اول و سوم نادیده گرفته می‌شوند.
$randomPart1 = getRandomLetters() . rand(100, 999); // ترکیبی از حروف تصادفی و یک عدد تصادفی
$randomPart2 = getRandomLetters() . rand(100, 999);
$computed_license = $randomPart1 . '-' . (string)$base . '-' . $randomPart2;

// متغیرهای وضعیت لایسنس
$license_active = true;   // اگر لایسنس فعال باشد، true است؛ در غیر این صورت false.
$disable_form   = false;  // اگر لایسنس غیر فعال باشد، فرم غیر فعال می‌شود.

// ادامه کد بررسی وضعیت راکر و ثبت لایسنس بدون تغییر باقی می‌ماند...

// بررسی وضعیت دکمه راکر اگر کلید لایسنس قبلی موجود باشد
if ( ! empty( $saved_license_key ) ) {
    $endpoint = "https://commart.ir/wp-json/commart_guard/v1/rocker-status/" . urlencode( $saved_license_key );
    $response = wp_remote_get( $endpoint, array( 'timeout' => 15 ) );
    if ( ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( is_array( $data ) && isset( $data['rocker'] ) && $data['rocker'] === 'off' ) {
            $license_active = false;
            $disable_form   = true;
            delete_option( 'plugin_example_saved_license_key' );
            $saved_license_key = '';
        }
    }
}

// کد بررسی وضعیت راکر در صورت عدم وجود کلید ثبت شده
if ( empty( $saved_license_key ) ) {
    if ( ! empty( $rocker_license_key ) ) {
        $endpoint = "https://commart.ir/wp-json/commart_guard/v1/rocker-status/" . urlencode( $rocker_license_key );
        $response = wp_remote_get( $endpoint, array( 'timeout' => 15 ) );
        if ( ! is_wp_error( $response ) ) {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body, true );
            if ( is_array( $data ) && isset( $data['rocker'] ) && $data['rocker'] === 'off' ) {
                $license_active = false;
                $disable_form   = true;
            }
        }
    }
}

// در صورتی که فرم ارسال شده باشد
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['license'] ) ) {
    if ( ! $license_active ) {
        echo '<p style="color:red;">امکان ثبت لایسنس وجود ندارد؛ وضعیت "راکر" off است. لطفاً ابتدا وضعیت را به "on" تغییر دهید.</p>';
    } else {
        $license_key = sanitize_text_field( $_POST['license'] );
        // استخراج قسمت وسط لایسنس (اعداد بدون حروف) که باید برابر با base باشد
        $parts = explode('-', $license_key);
        if(count($parts) < 3) {
            echo '<p style="color:red;">فرمت لایسنس نادرست است.</p>';
        } else {
            $license_middle = $parts[1];
            if ( $license_middle !== (string)$base ) {
                echo '<p style="color:red;">Invalid License Code. The expected license middle part is: ' . esc_html( (string)$base ) . '</p>';
            } else {
                $current_time = current_time( 'mysql' );
                $log_entry = sprintf( "[%s] License: %s%s", $current_time, $license_key, PHP_EOL );
                file_put_contents( $log_file, $log_entry, FILE_APPEND );
                update_option( 'plugin_example_saved_license_key', $license_key );
                update_option( 'rocker_status_license_key', $license_key );
                $saved_license_key = $license_key;
                $rocker_license_key = $license_key;
                echo '<p style="color:green;">License registered successfully.</p>';
                $plugin_name = 'Better me';
                $status = 'active';
                $body = array(
                    'plugin_name' => $plugin_name,
                    'site'        => $host_full,
                    'license'     => $license_key,
                    'status'      => $status
                );
                $response = wp_remote_post( 'https://commart.ir/wp-json/commart_guard/v1/register-license', array(
                    'method'  => 'POST',
                    'body'    => $body,
                    'timeout' => 15,
                ) );
                if ( is_wp_error( $response ) ) {
                    error_log( 'Error sending license info to Commart Guard: ' . $response->get_error_message() );
                }
            }
        }
    }
}
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) . 'css/manage-styles.css'; ?>">

<?php if ( $license_active ) : ?>
<div class="commart-guard-license-plugin">
    <form method="post">
        <div class="input-container" id="inputContainer">
            <input type="text" id="licenseInput" name="license" placeholder="Enter License" value="<?php echo esc_attr( $saved_license_key ); ?>" <?php if ( $disable_form ) echo 'disabled'; ?>>
            <button type="submit" <?php if ( $disable_form ) echo 'disabled'; ?>>Validate</button>
        </div>
    </form>
    <div class="info-box" style="color: #fff; margin-top: 10px;">
        <div>
            <span style="font-weight: bold;">License code:</span>
            <span><?php echo !empty( $rocker_license_key ) ? esc_html( $rocker_license_key ) : 'Not Registered'; ?></span>
        </div>
        <div>
            <span style="font-weight: bold;">URL:</span>
            <span><?php echo esc_html( $host_full ); ?></span>
        </div>
        <div>
            <span style="font-weight: bold;">Rocker Status:</span>
            <span id="rocker-status-text">Loading...</span>
        </div>
    </div>
</div>
<div class="commart-guard-license-blocked" style="display:none;">
    <button class="notallowed">
        <span>Not allowed!</span>
        <span>
           <img src="/wp-content/plugins/plugin-example/admin/icon-1.svg" alt="Not Allowed Icon" style="vertical-align:middle; width:16px; height:16px;">
        </span>
    </button>
    <p class="commart-guard-text-blocked">Your plugin has been blocked due to unauthorized use.</p>
</div>
<?php else : ?>
<div class="commart-guard-license-plugin" style="display:none;"></div>
<div class="commart-guard-license-blocked">
    <button class="notallowed">
        <span>Not allowed!</span>
        <span>
           <img src="/wp-content/plugins/plugin-example/admin/icon-1.svg" alt="Not Allowed Icon" style="vertical-align:middle; width:16px; height:16px;">
        </span>
    </button>
    <p class="commart-guard-text-blocked">Your plugin has been blocked due to unauthorized use.</p>
</div>
<?php endif; ?>

<?php if ( ! empty( $rocker_license_key ) ) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var license = "<?php echo esc_js( $rocker_license_key ); ?>";
    var endpoint = "https://commart.ir/wp-json/commart_guard/v1/rocker-status/" + encodeURIComponent(license);
    fetch(endpoint)
        .then(response => response.json())
        .then(data => {
            document.getElementById('rocker-status-text').textContent = data.rocker;
        })
        .catch(error => {
            console.error('Error fetching rocker status:', error);
            document.getElementById('rocker-status-text').textContent = "Error fetching status";
        });
});
</script>
<?php endif; ?>