<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define('WORAIS_LOGIN_PROTECT_CONFIGS', [
    'retention_time'    => 30,
    'ban_threshold'     => 15,
    'ban_time'          => 10,
    'captcha_show' => 1,
    'captcha_threshold' => 2
]);

define('WORAIS_LOGIN_PROTECT_CAPTCHA', [
    'width'  => 120,
    'height' => 40,
    'font'         => WORAIS_LOGIN_PROTECT_DIR . '/assets/monofont.ttf',
    'font_size'    => 40 * 0.75,
    'random_lines' => 20
]);

define('WORAIS_LOGIN_PROTECT_IP_HEADERS', [
    'HTTP_CF_CONNECTING_IP', // CloudFlare
    'HTTP_X_FORWARDED_FOR',  // AWS LB and other reverse-proxies
    'HTTP_X_REAL_IP',
    'HTTP_X_CLIENT_IP',
    'HTTP_CLIENT_IP',
    'HTTP_X_CLUSTER_CLIENT_IP'    
]);