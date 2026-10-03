<?php
// get_setting.php
require_once 'config.php';

function get_site_settings($pdo) {
    $settings = [];
    $sql = "SELECT setting_key, setting_value FROM site_config";
    if ($pdo) {
        // Removed non-standard space here
        $stmt = $pdo->query($sql);
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    
    $default_settings = [
        'site_title' => 'VIP Celebrity Booking',
        'use_site_logo' => '1',
        'site_logo' => 'assets/images/logo.png',
        'site_favicon' => 'assets/images/favicon.png',
        'hero_title' => 'The Ultimate Celebrity Experience',
        'hero_subtitle' => 'Book world-class talent for your next big event.',
        'hero_bg' => 'assets/img/hero-bg.jpg',
        'about_text' => 'We connect you with the biggest names in entertainment...',
        'about_image' => 'assets/img/about.jpg',
        'use_telegram' => '0',
        'telegram_username' => '@defaultusername',
        'use_whatsapp' => '0',
        'whatsapp_number' => '1234567890',
        'use_smartsupp' => '0',
        'smartsupp_code' => '',
        'brand_color' => '#4f46e5',
        'contact_email' => 'info@vipcelebrity.com',
        'contact_phone' => '+1 (555) 123-4567',
        'contact_address' => '123 Star Boulevard, Hollywood, CA',
        
        // --- SOCIAL LINKS ---
        'use_twitter' => '0',
        'twitter_url' => '',
        'social_twitter' => '#',
        'use_facebook' => '0',
        'facebook_url' => '',
        'social_facebook' => '#',
        'use_youtube' => '0',
        'youtube_url' => '',
        'use_instagram' => '0',
        'instagram_url' => '',
        'social_instagram' => '#',
        'social_linkedin' => '#',
        'footer_text' => '&copy; ' . date('Y') . ' VIP Celebrity Booking. All rights reserved.',
        
        // --- PAYMENT SETTINGS ---
        'bank_name' => '',
        'bank_account_name' => '',
        'bank_account_number' => '',
        'bank_sort_code' => '',
        'crypto_ltc' => '',
        'crypto_eth' => '',
        'crypto_usdt' => '',
        'crypto_btc' => '',
        'giftcard_note' => 'Contact support for gift card details.',
        'use_bank' => '0',
        'use_crypto' => '0',
        'use_giftcard' => '0',

        // Currency & Pricing Settings
        'currency_code' => 'USD',
        'currency_symbol' => '$',
        'currency_position' => 'prefix',
        
        'default_language' => 'en', // Default to English
        'maintenance_mode' => '0',
        'enable_cargo_scroll' => '0',
        'cargo_scroll_text' => 'VIP Celebrity Booking Services - Exclusive Talents Available Worldwide!',
        'enable_back_to_top' => '1',
        'disable_right_click_copy' => '0',
        
        // SMTP Settings
        'use_smtp' => '0',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => '587',
        'smtp_username' => 'your_email@example.com',
        'smtp_password' => 'your_email_password',
        'smtp_encryption' => 'tls',
        'smtp_from_email' => 'noreply@vipcelebrity.com',
        'smtp_from_name' => 'VIP Celebrity Booking',
        
        // Payment Gateway Settings
        'paystack_public_key' => '',
        'paystack_secret_key' => ''
    ];
    
    $site_settings = array_merge($default_settings, $settings);
    
    // Dynamically generate social links if they are empty
    $brand_slug = str_replace(' ', '', $site_settings['site_title'] ?? 'VIPCelebrityBooking');
    if (empty($site_settings['twitter_url'])) $site_settings['twitter_url'] = "https://twitter.com/" . $brand_slug;
    if (empty($site_settings['facebook_url'])) $site_settings['facebook_url'] = "https://facebook.com/" . $brand_slug;
    if (empty($site_settings['youtube_url'])) $site_settings['youtube_url'] = "https://youtube.com/" . $brand_slug;
    if (empty($site_settings['instagram_url'])) $site_settings['instagram_url'] = "https://instagram.com/" . $brand_slug;

    return $site_settings;
}

// Load all settings from the database (merged with defaults)
$site_settings = get_site_settings($pdo);

// --- Branding & Logo Paths ---
$use_logo = (
    ($site_settings['use_site_logo'] === '1') &&
    !empty($site_settings['site_logo'])
);

if ($use_logo) {
    if (strpos($site_settings['site_logo'], '/') !== false) {
        $logo_path = htmlspecialchars($site_settings['site_logo']);
    } else {
        $logo_path = 'assets/images/' . htmlspecialchars($site_settings['site_logo']);
    }
} else {
    $logo_path = '';
}

$use_favicon = !empty($site_settings['site_favicon']);
if ($use_favicon) {
    if (strpos($site_settings['site_favicon'], '/') !== false) {
        $favicon_path = htmlspecialchars($site_settings['site_favicon']);
    } else {
        $favicon_path = 'assets/images/' . htmlspecialchars($site_settings['site_favicon']);
    }
} else {
    $favicon_path = 'assets/images/favicon.png';
    $use_favicon = true;
}

$admin_favicon_path = '../' . ltrim($favicon_path, '/');

$favicon_mime = 'image/x-icon';

if ($use_favicon) {
    $extension = pathinfo($favicon_path, PATHINFO_EXTENSION);
    $extension = strtolower($extension);

    switch ($extension) {
        case 'png':
            $favicon_mime = 'image/png';
            break;
        case 'jpg':
        case 'jpeg':
            $favicon_mime = 'image/jpeg';
            break;
        case 'gif':
            $favicon_mime = 'image/gif';
            break;
        case 'svg':
            $favicon_mime = 'image/svg+xml';
            break;
        case 'ico':
            $favicon_mime = 'image/x-icon';
            break;
    }
}


// --- Chat/Social Links ---
$telegram_enabled = (
    $site_settings['use_telegram'] === '1' &&
    !empty($site_settings['telegram_username'])
);
$telegram_username_clean = ltrim(htmlspecialchars($site_settings['telegram_username']), '@');
$telegram_link = $telegram_enabled ? 'https://t.me/' . $telegram_username_clean : '';

$whatsapp_enabled = (
    $site_settings['use_whatsapp'] === '1' &&
    !empty($site_settings['whatsapp_number'])
);
$whatsapp_number_clean = preg_replace('/\D/', '', $site_settings['whatsapp_number']);
$whatsapp_link = $whatsapp_enabled ? 'https://wa.me/' . $whatsapp_number_clean : '';

$smartsupp_enabled = (
    $site_settings['use_smartsupp'] === '1' &&
    !empty($site_settings['smartsupp_code'])
);
$smartsupp_script = $smartsupp_enabled ? $site_settings['smartsupp_code'] : '';

// Generic Live Chat variables mapped from the same database keys
$livechat_enabled = $smartsupp_enabled;
$livechat_script = $smartsupp_script;

$brand_color = htmlspecialchars($site_settings['brand_color']);

$contact_email = htmlspecialchars($site_settings['contact_email'] ?? 'info@vipcelebrity.com');
$twitter_enabled = (
    $site_settings['use_twitter'] === '1' &&
    !empty($site_settings['twitter_url'])
);
$twitter_url = htmlspecialchars($site_settings['twitter_url']);

$facebook_enabled = (
    $site_settings['use_facebook'] === '1' &&
    !empty($site_settings['facebook_url'])
);
$facebook_url = htmlspecialchars($site_settings['facebook_url']);

$youtube_enabled = (
    $site_settings['use_youtube'] === '1' &&
    !empty($site_settings['youtube_url'])
);
$youtube_url = htmlspecialchars($site_settings['youtube_url']);

$instagram_enabled = (
    $site_settings['use_instagram'] === '1' &&
    !empty($site_settings['instagram_url'])
);
$instagram_url = htmlspecialchars($site_settings['instagram_url']);

// --- Payment Settings ---
$bank_enabled = $site_settings['use_bank'] === '1';
$bank_name = htmlspecialchars($site_settings['bank_name']);
$bank_account_name = htmlspecialchars($site_settings['bank_account_name']);
$bank_account_number = htmlspecialchars($site_settings['bank_account_number']);
$bank_sort_code = htmlspecialchars($site_settings['bank_sort_code']);

$crypto_enabled = $site_settings['use_crypto'] === '1';
$crypto_ltc = htmlspecialchars($site_settings['crypto_ltc']);
$crypto_eth = htmlspecialchars($site_settings['crypto_eth']);
$crypto_usdt = htmlspecialchars($site_settings['crypto_usdt']);
$crypto_btc = htmlspecialchars($site_settings['crypto_btc']);

$giftcard_enabled = $site_settings['use_giftcard'] === '1';
$giftcard_note = htmlspecialchars($site_settings['giftcard_note']);

// NEW: Language Variables
$default_language_code = htmlspecialchars($site_settings['default_language'] ?? 'en');





// --- MAINTENANCE MODE GLOBAL CHECK ---
// 1. Only check if maintenance mode is active (set to '1')
// 2. Exemptions: 
//    - Users already on maintenance.php
//    - Users in the admin directory (admin/)
//    - Logged-in admin users
if (($site_settings['maintenance_mode'] ?? '0') === '1') {
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    $is_admin_dir = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
    $is_admin_logged_in = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
    
    if ($current_script !== 'maintenance.php' && !$is_admin_dir && !$is_admin_logged_in) {
        header("Location: maintenance.php");
        exit;
    }
}

// --- GLOBAL CURRENCY FORMATTER HELPER ---
if (!function_exists('format_currency')) {
    function format_currency($amount, $settings = null, $decimals = null) {
        global $site_settings;
        if ($settings === null) {
            $settings = $site_settings ?? [];
        }
        $symbol = !empty($settings['currency_symbol']) ? $settings['currency_symbol'] : '$';
        $position = $settings['currency_position'] ?? 'prefix';
        if ($decimals === null) {
            $decimals = ((float)$amount == floor((float)$amount)) ? 0 : 2;
        }
        $formatted_number = number_format((float)$amount, $decimals);
        
        if ($position === 'suffix') {
            return $formatted_number . ' ' . $symbol;
        } else {
            return $symbol . $formatted_number;
        }
    }
}
?>