<?php
use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . '/../wp-load.php';
require_once dirname(__FILE__) . '/../wp-content/plugins/maggu-login-protect/maggu-login-protect.php';

class LoginProtectTest extends TestCase{
    
    public function testGetIpReturnsValidIp() {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        
        $ip = MagguLoginProtect::get_ip();
        
        $this->assertEquals('192.168.1.1', $ip);
    }
}