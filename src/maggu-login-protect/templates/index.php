<link rel='stylesheet' href='<?php echo MAGGU_LOGIN_PROTECT_URL; ?>assets/style.css' media='all' />

<?php
$config = get_option('maggu-login-protect');
?>
<div id="maggu-login-protect">
    <div id="header" style="background: #fff;padding: 20px;margin-left: -20px;border-bottom: 1px solid #e1e1e1;">
        <img src="<?php echo MAGGU_LOGIN_PROTECT_URL; ?>assets/logo.png" style="width: 32px;float: right;background: #fff;margin-top: -5px;"/>
        <h1 style="margin: 0;color: #333;"><?php echo __('Login Protect', 'maggu-login-protect' ); ?></h1>    
    </div>
    <div class="container logs">
        <h2><?php echo __('Logs:', 'maggu-login-protect' ); ?></h2>
        <ul>
            <?php    
            $logs = $wpdb->get_results("SELECT * FROM `maggu_login_protect` ORDER BY `datetime` DESC");
            foreach ($logs as $log) {
                $color = ($log->status == 0)? '#900' : '#090';

                echo "<li style='color: $color;'>[$log->ip -> $log->datetime]: $log->user_login</li>";
            }
            ?>
        </ul>
    </div>

    <div class="panel">
        <div class="container configs">
            <h2><?php echo __('Global Settings:', 'maggu-login-protect' ); ?></h2>
            <section>
                <h3><?php echo __('Logging:', 'maggu-login-protect' ); ?></h3>
                <div>
                    <label><?php echo __('Retention time:', 'maggu-login-protect' ); ?></label>
                    <i><?php echo __('days', 'maggu-login-protect' ); ?></i>
                    <input name="retention_time" type="number" step="1" min="0" value="<?php echo $config['retention_time']; ?>">
                    <span><?php echo __('Time in days that we must save the data.', 'maggu-login-protect' ); ?></span>
                </div>
            </section>
            <section>
                <h3><?php echo __('Lockouts:', 'maggu-login-protect' ); ?></h3>
                <div>
                    <label><?php echo __('Ban Threshold:', 'maggu-login-protect' ); ?></label>
                    <i><?php echo __('attempts', 'maggu-login-protect' ); ?></i>
                    <input name="ban_threshold" type="number" step="1" min="0" value="<?php echo $config['ban_threshold']; ?>">
                    <span><?php echo __('Number of attempts to block the attacker.', 'maggu-login-protect' ); ?></span>
                    <br /><br />
                    
                    <label><?php echo __('Ban Time:', 'maggu-login-protect' ); ?></label>
                    <i><?php echo __('minutes', 'maggu-login-protect' ); ?></i>
                    <input name="ban_time" type="number" step="1" min="0" value="<?php echo $config['ban_time']; ?>">
                    <span><?php echo __('Login attempts blocking time', 'maggu-login-protect' ); ?></span>                    
                </div>               
            </section>  
            
            <button class="btn-lg btn-primary" id="btn-configs-save"><span class="spinner is-active"></span><?php echo __('Save', 'maggu-login-protect' ); ?></button>
        </div>
    <div>
    <div id="result"></div>
    <div id="footer">
        <p id="footer-left" class="alignleft">        
		    <a href="https://github.com/worais/maggu-login-protect" target="_blank">Login Protect</a> <?php echo __('is developed and maintained by', 'maggu-login-protect' ); ?> <a href="https://maggu.org" target="_blank">Maggu</a>
        </p>
    </div>
<div>
<script>
jQuery('#btn-configs-save').click(function(){
    const $btn = jQuery(this);
    const data = { action: 'maggu-login-protect-config_save' };
    jQuery('#maggu-login-protect .configs').find('input, textarea, select').each(function(x, field) {
        data[field.name] = field.value;
    });

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data,                                         
        beforeSend:function() {
            $btn.addClass('loading');
            $btn.removeClass('error');
        },                
        success:function(data) {
            jQuery('#result').html(data);
        },   
        error:function(xhr, ajaxOptions, thrownError) {
            jQuery('#result').html(`<div class='error'>${thrownError}</div>`);
        },
        complete:function() {
            $btn.removeClass('loading');
        },                                                             
    })    
})
</script>