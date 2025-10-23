<?php
/**
 * MijSys CMS - Plik Konfiguracyjny
 * 
 * @package MijSysCMS
 * @version 1.0
 */

// Zapobieganie bezpośredniemu dostępowi
if (!defined('MIJSYS_CMS')) {
    define('MIJSYS_CMS', true);
}

// ==========================================
// KONFIGURACJA BAZY DANYCH
// ==========================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'mijsys_cms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// ==========================================
// KONFIGURACJA STRONY
// ==========================================

define('SITE_URL', 'http://localhost/mijsys-cms');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_URL', SITE_URL . '/uploads');

// ==========================================
// ŚCIEŻKI KATALOGÓW
// ==========================================

define('ROOT_PATH', dirname(__FILE__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('PLUGINS_PATH', ROOT_PATH . '/plugins');
define('THEMES_PATH', ROOT_PATH . '/themes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// ==========================================
// BEZPIECZEŃSTWO
// ==========================================

// Klucz soli do szyfrowania (ZMIEŃ NA UNIKALNY!)
define('SECURITY_SALT', 'zmien_mnie_na_unikalny_ciag_znakow_' . md5(__FILE__));

// Czas życia sesji (w sekundach) - 2 godziny
define('SESSION_LIFETIME', 7200);

// Maksymalna liczba prób logowania
define('MAX_LOGIN_ATTEMPTS', 5);

// Czas blokady po przekroczeniu prób (w sekundach) - 15 minut
define('LOGIN_LOCKOUT_TIME', 900);

// ==========================================
// USTAWIENIA PLIKÓW
// ==========================================

// Maksymalny rozmiar pliku (w bajtach) - 10MB
define('MAX_UPLOAD_SIZE', 10485760);

// Dozwolone typy plików
define('ALLOWED_UPLOAD_TYPES', serialize([
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'application/zip'
]));

// ==========================================
// TRYB DEWELOPERSKI
// ==========================================

// Ustaw na true tylko w środowisku deweloperskim!
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ==========================================
// STREFA CZASOWA
// ==========================================
date_default_timezone_set('Europe/Warsaw');

// ==========================================
// AUTOMATYCZNE ŁADOWANIE KLAS
// ==========================================
spl_autoload_register(function($class) {
    $file = INCLUDES_PATH . '/' . strtolower($class) . '.class.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ==========================================
// INICJALIZACJA SESJI
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Ustaw na 1 jeśli używasz HTTPS
    session_name('MIJSYS_SESSION');
    session_start();
}

// ==========================================
// FUNKCJE POMOCNICZE
// ==========================================

if (file_exists(INCLUDES_PATH . '/functions.php')) {
    require_once INCLUDES_PATH . '/functions.php';
}