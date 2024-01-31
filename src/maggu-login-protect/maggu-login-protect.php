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
        include dirname( __FILE__ ) . "/templates/index.php";
    }
}

register_activation_hook( __FILE__, ['MagguLoginProtect','install']);

add_action('admin_menu', ['MagguLoginProtect', 'menu']);