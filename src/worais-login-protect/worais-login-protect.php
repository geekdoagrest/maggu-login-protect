<?php
/**
 * Plugin Name: Worais Login Protect
 * Plugin URI: https://worais.com/login-protect
 * Description: Protect WordPress from unwanted login attempts
 * Author: Morais Junior
 * Author URI: https://worais.com
 * Version: 1.0.0
 * Requires at least: 5.6
 * Tested up to: 6.4
 * Text Domain: worais-login-protect
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

define('WORAIS_LOGIN_PROTECT_URL', plugin_dir_url( __FILE__ ));
define('WORAIS_LOGIN_PROTECT_DIR', dirname( __FILE__ ));

require WORAIS_LOGIN_PROTECT_DIR . "/consts/options.php";

class WORAISLoginProtect{
    public static function install(){
        global $wpdb;

        $wpdb->query('DROP TABLE IF EXISTS `worais_login_protect`;');
        $wpdb->query('COMMIT;');
        $wpdb->query('CREATE TABLE `worais_login_protect` (
            `user_login` varchar(60) NOT NULL,
            `ip` varchar(60) NOT NULL,
            `status` int(11) NOT NULL,
            `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX USING BTREE(`datetime`)
            ) ENGINE=MEMORY DEFAULT CHARSET=latin1;');

        add_option( 'worais-login-protect' , WORAIS_LOGIN_PROTECT_CONFIGS );
    }

    public static function menu(){
		add_options_page(
			__( 'Login Protect', 'worais-login-protect' ),
			__( 'Login Protect', 'worais-login-protect' ),
			'manage_options',
			'worais-login-protect',
			['WoraisLoginProtect', 'page']
		);
    }

    public static function page(){
        global $wpdb;

        include WORAIS_LOGIN_PROTECT_DIR . "/templates/index.php";
    }

    public static function log( $username ){
        global $wpdb;

        if(empty($username )){ return false; }

        $status = (int)('wp_login' == current_filter());
        $ip = apply_filters( 'worais_get_ip', $_SERVER);

        $wpdb->insert('worais_login_protect', [
            'user_login' => $username,
            'status'     => $status,
            'ip'         => $ip,
        ], ['%s', '%d', '%s']);
    }

    public static function waf(){
        global $wpdb;

        $ip = apply_filters( 'worais_get_ip', $_SERVER);
        $config = get_option('worais-login-protect');
        $config = apply_filters( 'worais-login-protect-config', $config);

        //clear old data
        $wpdb->get_var( $wpdb->prepare(
            "DELETE FROM `worais_login_protect` WHERE `datetime` < NOW() - INTERVAL %d DAY", $config['retention_time']
        ));

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM `worais_login_protect` WHERE 
            `datetime` > NOW() - INTERVAL %d MINUTE AND 
            `ip` = %s AND 
            `status` = 0;", $config['ban_time'], $ip));

        if( $count >= $config['ban_threshold'] ){
            include dirname( __FILE__ ) . "/templates/blocked.php";
            exit;
        }

        if( (bool) $config['captcha_show'] && $count >= $config['captcha_threshold'] ){
            require WORAIS_LOGIN_PROTECT_DIR . "/includes/worais-login-captcha.php";
        }
    }

    public static function form(){
        $ip = apply_filters( 'worais_get_ip', $_SERVER);

        echo __( 'Your IP:', 'worais-login-protect' );
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
        $data = get_option('worais-login-protect');
        foreach( WORAIS_LOGIN_PROTECT_CONFIGS as $key => $value ){
            $data[$key] = $_POST[$key];
        }

        delete_option( 'worais-login-protect' );

        $action = add_option( 'worais-login-protect' , $data );
        if( $action ){
            echo "<div class='updated'>".__('Saved!', 'worais-login-protect' )."</div>";
        } else {
            echo "<div class='error'>".__('Error!', 'worais-login-protect' )."</div>";
        }        
        exit;
    }

	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'worais-login-protect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}  

    public static function load_plugin_action_links($links){
      $settings_link = '<a href="' . admin_url('options-general.php?page=worais-login-protect') . '" title="Settings">Settings</a>';
  
      array_unshift($links, $settings_link);
  
      return $links;
    } 
}

register_activation_hook( __FILE__, ['WoraisLoginProtect','install']);

add_action('admin_menu',      ['WoraisLoginProtect', 'menu']);
add_action('wp_login',        ['WoraisLoginProtect', 'log']);
add_action('wp_login_failed', ['WoraisLoginProtect', 'log']);
add_action('login_form_login',['WoraisLoginProtect', 'waf']);
add_action('login_form',      ['WoraisLoginProtect', 'form']);
add_action('plugins_loaded',  ['WoraisLoginProtect', 'load_plugin_textdomain'] );

add_action('wp_ajax_worais-login-protect-config_save', ['WoraisLoginProtect', 'config_save']);

add_filter('worais_get_ip',                  ['WoraisLoginProtect', 'get_ip']);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), ['WoraisLoginProtect', 'load_plugin_action_links']);