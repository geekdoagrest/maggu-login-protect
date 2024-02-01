<link rel='stylesheet' href='<?php echo MAGGU_LOGIN_PROTECT_URL; ?>css/style.css' media='all' />
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
            <h2>Configs: </h2>
        </div>
    <div>
    <div id="footer">
    <p id="footer-left" class="alignleft">
		<a href="https://github.com/worais/maggu-login-protect" target="_blank">Login Protect</a> is developed and maintained by <a href="https://maggu.org" target="_blank">Maggu Project</a></p>
    </div>
<div>