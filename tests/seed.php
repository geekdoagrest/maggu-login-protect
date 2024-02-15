<?php
require_once dirname(__FILE__) . '/../wp-load.php';
require_once dirname(__FILE__) . '/../wp-content/plugins/worais-login-protect/worais-login-protect.php';

global $wpdb;

$number_of_records = 5000;
for ($i = 0; $i < $number_of_records; $i++) {
    $fake_user = 'user' . rand(0, 10);
    $fake_status = rand(0, 1);
    $fake_ip = '192.168.' . rand(1, 255) . '.' . rand(1, 255);
    $fake_datetime = date('Y-m-d H:i:s', strtotime("-" . rand(1, 30) . " days")); 

    $wpdb->insert('worais_login_protect', [
        'user_login' => $fake_user,
        'status'     => $fake_status,
        'ip'         => $fake_ip,
        'datetime'   => $fake_datetime,
    ], ['%s', '%d', '%s', '%s']);

    echo "$fake_user -> $fake_ip\n";
}

