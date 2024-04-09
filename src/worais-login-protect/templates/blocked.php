<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<link rel='stylesheet' href='<?php echo esc_html(sanitize_url(WORAIS_LOGIN_PROTECT_URL)); ?>assets/style.css' media='all' />
<div id="blocked">
    <h1><?php esc_html_e('Blocked!', 'worais-login-protect' ); ?></h1>
    <h2><?php esc_html_e('You have exceeded the maximum number of attempts!', 'worais-login-protect' ); ?></h2>
</div>