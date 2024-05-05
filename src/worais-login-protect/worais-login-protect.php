<?php
/**
 * Plugin Name: Worais Login Protect
 * Plugin URI: https://github.com/worais/login-protect
 * Description: Protect WordPress from unwanted login attempts
 * Author: Morais Junior
 * Author URI: https://github.com/worais/
 * Version: 1.1.0
 * Requires at least: 5.6
 * Tested up to: 6.5
 * Text Domain: worais-login-protect
 * Domain Path: /languages/
 * License: GPLv3 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;
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
			esc_html__( 'Login Protect', 'worais-login-protect' ),
			esc_html__( 'Login Protect', 'worais-login-protect' ),
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
        $ip = esc_html(sanitize_text_field($_SERVER['REMOTE_ADDR']));      
        $ip = apply_filters('worais_get_ip', $ip);

        $wpdb->insert('worais_login_protect', [
            'user_login' => $username,
            'status'     => $status,
            'ip'         => $ip,
        ], ['%s', '%d', '%s']);
    }

    public static function waf(){
        global $wpdb;        
        $ip = esc_html(sanitize_text_field($_SERVER['REMOTE_ADDR']));      
        $ip = apply_filters( 'worais_get_ip', $ip);


        $config = get_option('worais-login-protect', WORAIS_LOGIN_PROTECT_CONFIGS);
        $config = apply_filters('worais-login-protect-config', $config);

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
        $ip = esc_html(sanitize_text_field($_SERVER['REMOTE_ADDR']));      
        $ip = apply_filters('worais_get_ip', $ip);

        esc_html_e( 'Your IP:', 'worais-login-protect' );
        echo " <i>".esc_html(sanitize_text_field($ip))."</i><br /><br />";
    }

    public static function scripts(){
        wp_enqueue_style('worais-login-protect-style', plugin_dir_url( __FILE__ ).'/assets/style.css');
        wp_enqueue_script('worais-login-protect-chart', plugin_dir_url( __FILE__ ).'/assets/chart.js');
    }

    public static function get_ip($ip){
        foreach (WORAIS_LOGIN_PROTECT_IP_HEADERS as $header) {
            if (array_key_exists($header, $_SERVER)) {
                $ip = esc_html(sanitize_text_field($_SERVER[$header]));
                $ip = trim(explode(',', $ip)[0]);
            }
        }  
        
        return $ip;
    }

    public static function config_save(){
        if(!check_admin_referer('worais-login-protect-config')){
            die();
        }

        $data = get_option('worais-login-protect');
        foreach( WORAIS_LOGIN_PROTECT_CONFIGS as $key => $value ){
            $data[$key] = esc_html(sanitize_text_field($_POST[$key]));
        }

        delete_option('worais-login-protect');

        $action = add_option('worais-login-protect' , $data);
        if( $action ){
            echo "<div class='updated'>".esc_html__('Saved!', 'worais-login-protect' )."</div>";
        } else {
            echo "<div class='error'>".esc_html__('Error!', 'worais-login-protect' )."</div>";
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

add_action('init',               ['WoraisLoginProtect', 'scripts']);
add_action('admin_menu',         ['WoraisLoginProtect', 'menu']);
add_action('wp_login',           ['WoraisLoginProtect', 'log']);
add_action('wp_login_failed',    ['WoraisLoginProtect', 'log']);
add_action('login_form_login',   ['WoraisLoginProtect', 'waf']);
add_action('login_form',         ['WoraisLoginProtect', 'form']);
add_action('plugins_loaded',     ['WoraisLoginProtect', 'load_plugin_textdomain'] );

add_action('wp_ajax_worais-login-protect-config_save', ['WoraisLoginProtect', 'config_save']);

add_filter('worais_get_ip',                  ['WoraisLoginProtect', 'get_ip']);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), ['WoraisLoginProtect', 'load_plugin_action_links']);