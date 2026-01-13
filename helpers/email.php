<?php
/**
 * Email Configuration
 * SMTP settings for sending emails
 */

// Email configuration
define('SMTP_HOST', 'mail.rpirvulescu.daw.ssmr.ro');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
define('SMTP_USERNAME', 'rpirvule@rpirvulescu.daw.ssmr.ro');
define('SMTP_PASSWORD', 'UU)s9uY47qp3X*');
define('SMTP_FROM_EMAIL', 'rpirvule@rpirvulescu.daw.ssmr.ro');
define('SMTP_FROM_NAME', 'Biblioteca Online');

// Email settings
define('SMTP_DEBUG', 0); // 0 = off, 1 = client messages, 2 = client and server messages
define('SMTP_CHARSET', 'UTF-8');

// Whitelist emails that skip 2FA verification
define('WHITELIST_EMAILS', [
    'admin@rpirvulescu.daw.ssmr.ro',
    'manager@rpirvulescu.daw.ssmr.ro',
    'user@rpirvulescu.daw.ssmr.ro'
]);
