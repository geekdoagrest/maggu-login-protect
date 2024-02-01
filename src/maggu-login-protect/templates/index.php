<link rel='stylesheet' href='<?php echo MAGGU_LOGIN_PROTECT_URL; ?>assets/style.css' media='all' />

<?php
$config = get_option('maggu-login-protect');
?>
<div id="maggu-login-protect">
    <div id="header" style="background: #fff;padding: 20px;margin-left: -20px;border-bottom: 1px solid #e1e1e1;">
        <img src="<?php echo MAGGU_LOGIN_PROTECT_URL; ?>assets/logo.png" style="width: 32px;float: right;background: #fff;margin-top: -5px;"/>
        <h1 style="margin: 0;color: #333;">Login Protect</h1>    
    </div>
    <div class="container logs">
        <h2>Logs: </h2>
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
            <h2>Global Settings: </h2>
            <section>
                <h3>Logging:</h3>
                <div>
                    <label>Retention time:</label>
                    <i>days</i>
                    <input name="retention_time" type="number" step="1" min="0" value="<?php echo $config['retention_time']; ?>">
                    <span>The number of lockouts Solid Security must remember before permanently banning the attacker.</span>
                </div>
            </section>
            <section>
                <h3>Lockouts:</h3>
                <div>
                    <label>Ban Threshold:</label>
                    <i>attempts</i>
                    <input name="ban_threshold" type="number" step="1" min="0" value="<?php echo $config['ban_threshold']; ?>">
                    <span>The number of days database logs should be kept.</span>
                    <br /><br />
                    
                    <label>Ban Time:</label>
                    <i>minutes</i>
                    <input name="ban_time" type="number" step="1" min="0" value="<?php echo $config['ban_time']; ?>">
                    <span>Login attempts blocking time</span>                    
                </div>               
            </section>  
            
            <button class="btn-lg btn-primary" id="btn-configs-save"> Save </button>
        </div>
    <div>
    <div id="result"></div>
    <div id="footer">
        <p id="footer-left" class="alignleft">        
		    <a href="https://github.com/worais/maggu-login-protect" target="_blank">Login Protect</a> is developed and maintained by <a href="https://maggu.org" target="_blank">Maggu</a>
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
            $btn.addClass('error');
            jQuery('#result').html(`<div class='error'>${thrownError}</div>`);
        },
        complete:function() {
            $btn.removeClass('loading');
        },                                                             
    })    
})
</script>