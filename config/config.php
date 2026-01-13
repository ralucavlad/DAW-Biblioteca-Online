<?php
/**
 * Online Library Application Configuration
 */

// =====================================================
// DATABASE CONFIGURATION
// =====================================================

// Production environment (rpirvulescu.daw.ssmr.ro)
define('DB_HOST', 'localhost');
define('DB_NAME', 'rpirvule_test');
define('DB_USER', 'rpirvule_test');
define('DB_PASS', 'MfFzHgSn67uFqECU92nd');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// reCAPTCHA CONFIGURATION
// =====================================================

define('RECAPTCHA_SITE_KEY', '6LfWczcsAAAAAB-y0dMqg_lxBsPClgNYMlwXofRT');
define('RECAPTCHA_SECRET_KEY', '6LfWczcsAAAAAJEhFiKsmBdxVtnwJ9QIqH802-V2');

// =====================================================
// TIMEZONE & LOCALE
// =====================================================

date_default_timezone_set('Europe/Bucharest');
setlocale(LC_TIME, 'ro_RO.UTF-8');

?>
