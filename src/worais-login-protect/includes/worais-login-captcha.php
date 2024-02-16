<?php
/*
    Extracted and adapted from: https://wordpress.org/plugins/worais-login-protect/
    thanks WebFactory Ltd ;)
*/
session_start();
class WoraisLoginCaptcha{
    public static function captcha_for_login(){
        self::captcha_image_generate();

        echo '<label>' . esc_html__('Type the text displayed above:', 'worais-login-protect') . '</label>
                <input id="captcha_code" name="captcha_code" size="15" type="text" tabindex="30" />
                </p>';

        if (isset($_GET['captcha']) && $_GET['captcha'] == 'confirm_error') {
            echo '<label style="color:#FF0000;" id="capt_err">' . esc_html($_SESSION['captcha_error']) . '</label><div style="clear:both;"></div>';;
            $_SESSION['captcha_error'] = '';
        }

        return true;
    }

    private static function captcha_hexrgb($hexstr){
      $int = hexdec($hexstr);
  
      return array(
        "red" => 0xFF & ($int >> 0x10),
        "green" => 0xFF & ($int >> 0x8),
        "blue" => 0xFF & $int
      );
    } 

    private static function captcha_image_generate(){
        $options = WORAIS_LOGIN_PROTECT_CAPTCHA;
        $possible_letters = array_merge(range('A', 'Z'), range('0', '9'));
        shuffle($possible_letters);

        $_SESSION['captcha_code'] = implode( '', array_slice( $possible_letters, 0, 5 ) );
    
        $image = @imagecreate($options['width'], $options['height']);
        imagecolorallocate($image, 255, 255, 255);
         
        $arr_text_color = self::captcha_hexrgb("0x142864");
        $text_color = imagecolorallocate( $image,
          $arr_text_color['red'],
          $arr_text_color['green'],
          $arr_text_color['blue']
        );
    
        $arr_noice_color = self::captcha_hexrgb("0x142864");
        $image_noise_color = imagecolorallocate( $image,
          $arr_noice_color['red'],
          $arr_noice_color['green'],
          $arr_noice_color['blue']
        );

        for ($i = 0; $i < $options['random_lines']; $i++)
          imageline($image, mt_rand(0, $options['width']), mt_rand(0, $options['height']), mt_rand(0, $options['width']), mt_rand(0, $options['height']), $image_noise_color);
    
        $textbox = imagettfbbox($options['font_size'], 0, $options['font'], $_SESSION['captcha_code']);
        $x = ($options['width'] - $textbox[4]) / 2;
        $y = ($options['height'] - $textbox[5]) / 2;
        imagettftext($image, $options['font_size'], 0, (int)$x, (int)$y, $text_color, $options['font'], $_SESSION['captcha_code']);
    
        ob_start();
        imagejpeg($image);
        echo '<img src="data:image/png;base64,' . esc_html(base64_encode(ob_get_clean())) . '" width="100">';
        imagedestroy($image);
    }

    public static function captcha_login_errors($errors){
      if (isset($_REQUEST['action']) && 'register' == sanitize_text_field($_REQUEST['action'])) {
        return ($errors);
      }

      if (isset($_SESSION['captcha_code']) && !isset($_REQUEST['captcha_code'])){
        return '<label>' . esc_html__('Validation is mandatory!', 'worais-login-protect') . '</label>';
      }
  
      if (isset($_SESSION['captcha_code']) && esc_html($_SESSION['captcha_code']) != sanitize_text_field($_REQUEST['captcha_code'])) {
        return '<label>' . esc_html__('Captcha confirmation error!', 'worais-login-protect') . '</label>';
      }

      return $errors;
    }    

    public static function captcha_login_redirect($url){
      if(!isset($_REQUEST['log'])){ return false; }

      if (empty($_REQUEST['captcha_code']) || (isset($_SESSION['captcha_code']) && esc_html($_SESSION['captcha_code']) != sanitize_text_field($_REQUEST['captcha_code']))) {
        $_SESSION['captcha_error'] = esc_html__('Incorrect captcha confirmation!', 'worais-login-protect');
        wp_clear_auth_cookie();

        WoraisLoginProtect::log( $_REQUEST['log'] );

        return $_SERVER["REQUEST_URI"] . "/?captcha='confirm_error'";
      }
      
      return get_admin_url();
    } 
}

add_action('login_form',      ['WoraisLoginCaptcha', 'captcha_for_login']);
add_filter('login_errors',    ['WoraisLoginCaptcha', 'captcha_login_errors']);
add_filter('login_redirect',  ['WoraisLoginCaptcha', 'captcha_login_redirect'], 10, 3);