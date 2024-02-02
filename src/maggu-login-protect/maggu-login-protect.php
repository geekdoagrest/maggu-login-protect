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

require dirname( __FILE__ ) . "/consts/options.php";

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

        add_option( 'maggu-login-protect' , MAGGU_LOGIN_PROTECT_CONFIGS );
    }

    public static function menu(){
		add_options_page(
			__( 'Login Protect', 'maggu-login-protect' ),
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
        ], ['%s', '%d', '%s']);
    }

    public static function waf(){
        global $wpdb;

        $ip = apply_filters( 'maggu_get_ip', $_SERVER);
        $config = get_option('maggu-login-protect');

        //clear old data
        $wpdb->get_var( $wpdb->prepare(
            "DELETE FROM `maggu_login_protect` WHERE `datetime` < NOW() - INTERVAL %d DAY", $config['retention_time']
        ));

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM `maggu_login_protect` WHERE 
            `datetime` > NOW() - INTERVAL %d MINUTE AND 
            `ip` = %s AND 
            `status` = 0;", $config['ban_time'], $ip));

        if( $count >= $config['ban_threshold'] ){
            include dirname( __FILE__ ) . "/templates/blocked.php";
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
        foreach( MAGGU_LOGIN_PROTECT_CONFIGS as $key => $value ){
            $data[$key] = $_POST[$key];
        }

        delete_option( 'maggu-login-protect' );

        $action = add_option( 'maggu-login-protect' , $data );
        if( $action ){
            echo "<div class='updated'>".__('Saved!', 'maggu-login-protect' )."</div>";
        } else {
            echo "<div class='error'>".__('Error!', 'maggu-login-protect' )."</div>";
        }        
        exit;
    }

	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'maggu-login-protect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}    
}

register_activation_hook( __FILE__, ['MagguLoginProtect','install']);

add_action('admin_menu',      ['MagguLoginProtect', 'menu']);
add_action('wp_login',        ['MagguLoginProtect', 'log']);
add_action('wp_login_failed', ['MagguLoginProtect', 'log']);
add_action('login_form',      ['MagguLoginProtect', 'form']);
add_action('login_form_login',['MagguLoginProtect', 'waf']);
add_action( 'plugins_loaded', ['MagguLoginProtect', 'load_plugin_textdomain'] );

add_action('wp_ajax_maggu-login-protect-config_save', ['MagguLoginProtect', 'config_save']);

add_filter('maggu_get_ip',                  ['MagguLoginProtect', 'get_ip']);