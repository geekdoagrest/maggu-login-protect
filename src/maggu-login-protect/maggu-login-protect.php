<?php
/**
 * Plugin Name: Maggu Login Protect
 * Plugin URI: https://maggu.org/login-protect
 * Description: Protect WordPress from unwanted login attempts
 * Author: Morais Junior
 * Author URI: https://maggu.org
 * Version: 1.0.0
 * Requires at least: 5.6
 * Tested up to: 6.4
 * Text Domain: maggu-login-protect
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

define('MAGGU_LOGIN_PROTECT_URL', plugin_dir_url( __FILE__ ));

class MagguLoginProtect{
    public static function install(){
        global $wpdb;

        $wpdb->query('DROP TABLE IF EXISTS `maggu_login_protect`;');
        $wpdb->query('COMMIT;');
        $wpdb->query('CREATE TABLE `maggu_login_protect` (
            `user_login` varchar(60) NOT NULL,
            `ip` varchar(60) NOT NULL,
            `status` int(11) NOT NULL,
            `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX USING BTREE(`datetime`)
            ) ENGINE=MEMORY DEFAULT CHARSET=latin1;');

        $config = [
            'retention_time' => 30,
            'ban_threshold' => 15,
            'ban_time' => 10
        ];

        add_option( 'maggu-login-protect' , $config );
    }

    public static function menu(){
		add_options_page(
			__( 'Maggu: Login Protect', 'maggu-login-protect' ),
			__( 'Login Protect', 'maggu-login-protect' ),
			'manage_options',
			'maggu-login-protect',
			['MagguLoginProtect', 'page']
		);
    }

    public static function page(){
        global $wpdb;

        include dirname( __FILE__ ) . "/templates/index.php";
    }

    public static function log( $username ){
        global $wpdb;

        $status = (int)('wp_login' == current_filter());
        $ip = apply_filters( 'maggu_get_ip', $_SERVER);

        $wpdb->insert('maggu_login_protect', [
            'user_login' => $username,
            'status'     => $status,
            'ip'         => $ip,
        ], ['%s', '%d']);
    }

    public static function waf(){
        global $wpdb;

        $ip = apply_filters( 'maggu_get_ip', $_SERVER);
        $config = get_option('maggu-login-protect');

        //clear old data
        $wpdb->get_var("DELETE FROM `maggu_login_protect` WHERE `datetime` < NOW() - INTERVAL $config[retention_time] DAY");

        $count = $wpdb->get_var("SELECT COUNT(*) FROM `maggu_login_protect` WHERE 
            `datetime` > NOW() - INTERVAL $config[ban_time] MINUTE AND 
            `ip` = '$ip' AND 
            `status` = 0;");

        if( $count >= $config['ban_threshold'] ){
            echo "Blocked!";
            exit;
        }
    }

    public static function form(){
        $ip = apply_filters( 'maggu_get_ip', $_SERVER);

        echo __( 'Your IP:', 'maggu-login-protect' );
        echo " <i>$ip</i><br /><br />";
    }

    public static function get_ip(){
        $headers = [
            'HTTP_CF_CONNECTING_IP', // CloudFlare
            'HTTP_X_FORWARDED_FOR',  // AWS LB and other reverse-proxies
            'HTTP_X_REAL_IP',
            'HTTP_X_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_CLUSTER_CLIENT_IP',
        ];
        
        foreach ($headers as $header) {
            if (array_key_exists($header, $_SERVER)) {
                $ip = $_SERVER[$header];
                
                // This line might or might not be used.
                $ip = trim(explode(',', $ip)[0]);
                
                return $ip;
            }
        }
        
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function config_save(){
        $data = get_option('maggu-login-protect');
        $data['retention_time'] = (int) $_POST['retention_time'];
        $data['ban_threshold']  = (int) $_POST['ban_threshold'];
        $data['ban_time']  = (int) $_POST['ban_time'];

        delete_option( 'maggu-login-protect' );

        $action = add_option( 'maggu-login-protect' , $data );
        if( $action ){
            echo "<div class='updated'>Saved!</div>";
        } else {
            echo "<div class='error'>Error!</div>";
        }        
        exit;
    }
}

register_activation_hook( __FILE__, ['MagguLoginProtect','install']);

add_action('admin_menu',      ['MagguLoginProtect', 'menu']);
add_action('wp_login',        ['MagguLoginProtect', 'log']);
add_action('wp_login_failed', ['MagguLoginProtect', 'log']);
add_action('login_form',      ['MagguLoginProtect', 'form']);
add_action('login_form_login',['MagguLoginProtect', 'waf']);

add_action('wp_ajax_maggu-login-protect-config_save', ['MagguLoginProtect', 'config_save']);

add_filter('maggu_get_ip',                  ['MagguLoginProtect', 'get_ip']);