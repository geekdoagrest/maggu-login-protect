<div id="header" style="background: #fff;padding: 20px;margin-left: -20px;border-bottom: 1px solid #e1e1e1;">
    <img src="<?php echo MAGGU_LOGIN_PROTECT_URL; ?>assets/settings.png" style="width: 32px;float: right;background: #fff;margin-top: -5px;"/>
    <h1 style="margin: 0;color: #333;">Maggu Login Protect</h1>    
</div>

<ul>
    <?php    
    $logs = $wpdb->get_results("SELECT * FROM `maggu_login_protect` ORDER BY `datetime` DESC");
    foreach ($logs as $log) {
        $color = ($log->status == 0)? '#900' : '#090';
        
        echo "<li style='color: $color;'>$log->datetime: $log->user_login</li>";
    }
    ?>
</ul>