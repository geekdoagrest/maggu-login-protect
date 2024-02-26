<link rel='stylesheet' href='<?php echo WORAIS_LOGIN_PROTECT_URL; ?>assets/style.css' media='all' />

<?php
$config = get_option('worais-login-protect');
$config = apply_filters('worais-login-protect-config', $config);
?>
<div id="worais-login-protect">
    <div id="header" style="background: #fff;padding: 20px;margin-left: -20px;border-bottom: 1px solid #e1e1e1;">
        <img src="<?php echo WORAIS_LOGIN_PROTECT_URL; ?>assets/logo.png" style="width: 32px;float: right;background: #fff;margin-top: -5px;"/>
        <h1 style="margin: 0;color: #333;"><?php esc_html_e('Login Protect', 'worais-login-protect' ); ?></h1>    
    </div>
    <div class="container logs">
        <h2><?php esc_html_e('Logs:', 'worais-login-protect' ); ?></h2>
        <ul>
            <?php    
            $logs = $wpdb->get_results("SELECT * FROM `worais_login_protect` ORDER BY `datetime` DESC LIMIT 100");

            if(is_array($logs) && empty($logs)){
                echo '<li class="empty"></li>';
            } else {
                foreach ($logs as $log) {
                    $color = ($log->status == 0)? '#900' : '#090';

                    echo "<li style='color: $color;'>[$log->ip -> $log->datetime]: $log->user_login</li>";
                }
            }            
            ?>
        </ul>
    </div>

    <div class="panel">
        <?php
            $logins = $wpdb->get_results("SELECT DATE(`datetime`) as day, `status`, COUNT(*) as sum
                FROM `worais_login_protect`
                GROUP BY day, `status`
                ORDER BY day, `status`;");

            if(is_array($logins) && !empty($logins)){
        ?>          
        <div class="container summary">
            <canvas id="summaryChart" style="max-height: 300px;"></canvas>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>          
            <script>
            const ctx = document.getElementById('summaryChart');

            new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [
                        {label: 'Logins', data: [
                            <?php
                                foreach ($logins as $log) {
                                    if($log->status == 1) { echo "{x: '$log->day', y: $log->sum},"; }
                                }
                            ?>]
                        },
                        {label: 'Lockouts', data: [
                            <?php
                                foreach ($logins as $log) {
                                    if($log->status == 0) { echo "{x: '$log->day', y: $log->sum},"; }
                                }
                            ?>]
                        }
                    ]
                },
            });
            </script>            
        </div>
        <?php } ?>
        <div class="container configs">
            <h2><?php esc_html_e('Settings:', 'worais-login-protect' ); ?></h2>
            <section>
                <h3><?php esc_html_e('Logging:', 'worais-login-protect' ); ?></h3>
                <div>
                    <label><?php esc_html_e('Retention time:', 'worais-login-protect' ); ?></label>
                    <i><?php esc_html_e('days', 'worais-login-protect' ); ?></i>
                    <input name="retention_time" type="number" step="1" min="0" value="<?php echo $config['retention_time']; ?>">
                    <span><?php esc_html_e('Time in days that we must save the data.', 'worais-login-protect' ); ?></span>
                </div>
            </section>
            <section>
                <h3><?php esc_html_e('Lockouts:', 'worais-login-protect' ); ?></h3>
                <div>
                    <label><?php esc_html_e('Ban Threshold:', 'worais-login-protect' ); ?></label>
                    <i><?php esc_html_e('attempts', 'worais-login-protect' ); ?></i>
                    <input name="ban_threshold" type="number" step="1" min="0" value="<?php echo $config['ban_threshold']; ?>">
                    <span><?php esc_html_e('Number of attempts to block the attacker.', 'worais-login-protect' ); ?></span>
                    <br /><br />
                    
                    <label><?php esc_html_e('Ban Time:', 'worais-login-protect' ); ?></label>
                    <i><?php esc_html_e('minutes', 'worais-login-protect' ); ?></i>
                    <input name="ban_time" type="number" step="1" min="0" value="<?php echo $config['ban_time']; ?>">
                    <span><?php esc_html_e('Login attempts blocking time', 'worais-login-protect' ); ?></span>                    
                </div>               
            </section>  
            <section>
                <h3><?php esc_html_e('Captcha:', 'worais-login-protect' ); ?></h3>
                <div>
                    <input type="checkbox" name="captcha_show" <?php echo ($config['captcha_show']) ? 'checked' : ''; ?>>
                    <label><?php esc_html_e('Enable:', 'worais-login-protect' ); ?></label>
                    <br /><br />

                    <label><?php esc_html_e('Show after:', 'worais-login-protect' ); ?></label>
                    <i><?php esc_html_e('attempts', 'worais-login-protect' ); ?></i>
                    <input name="captcha_threshold" type="number" step="1" min="0" value="<?php echo $config['captcha_threshold']; ?>">
                    <span><?php esc_html_e('Number of failed attempts before showing the captcha', 'worais-login-protect' ); ?></span>
                </div>
            </section>            


            <button class="btn-lg btn-primary" id="btn-configs-save"><span class="spinner is-active"></span><?php esc_html_e('Save', 'worais-login-protect' ); ?></button>
        </div>
    <div>
    <div id="result"></div>
    <div id="footer" class="worais-footer">
        <p id="footer-left" class="alignleft">        
		    <a href="https://github.com/worais/worais-login-protect" target="_blank">Login Protect</a> <?php esc_html_e('is developed and maintained by', 'worais-login-protect' ); ?> <a href="https://worais.com" target="_blank">Worais</a>
        </p>
    </div>
<div>
<script>
jQuery('#btn-configs-save').click(function(){
    const $btn = jQuery(this);
    const data = { action: 'worais-login-protect-config_save' };
    jQuery('#worais-login-protect .configs').find('input, textarea, select').each(function(x, field) {
        if(field.type == 'checkbox'){
            data[field.name] = (field.checked)? 1 : 0;
        } else {
            data[field.name] = field.value;
        }
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