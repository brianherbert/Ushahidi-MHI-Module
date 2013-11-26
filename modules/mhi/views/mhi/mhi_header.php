<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="SITE DESCRIPTION HERE" />
<meta name="keywords" content="SITE KEYWORDS HERE" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php echo Kohana::config("globalcode.head"); ?>
<title>Your Ushahidi MHI Site</title>
<?php
	echo html::stylesheet(url::file_loc('css').'media/mhi/css/reset','',true);

	echo "<!--[if lte IE 7]>".html::stylesheet(url::file_loc('css').'media/mhi/css/reset.ie','',true)."\n"."<![endif]-->";

	echo html::link('https://fonts.googleapis.com/css?family=Yanone+Kaffeesatz','stylesheet','text/css', false);

	echo html::stylesheet(url::file_loc('css').'media/mhi/css/base','',true);

	echo html::script(array(
		    url::file_loc('js').'media/mhi/js/jquery.min.js',
		    url::file_loc('js').'media/mhi/js/jquery.validate.min.js',
		    url::file_loc('js').'media/mhi/js/jquery.cycle.min.js',
		    url::file_loc('js').'media/mhi/js/jquery.colorbox.min.js',
            url::file_loc('js').'media/mhi/js/jquery.tagcloud.js',
		    url::file_loc('js').'media/mhi/js/initialize.js'
			), true);
?>

<?php if($js != '') { ?>
<script type="text/javascript" language="javascript">
<?php echo $js."\n"; ?>
</script>
<?php } ?>

<?php if($form_error === true) { ?>
<script type="text/javascript" language="javascript">
$(function(){
    //show the dagum form
    $("#login-form").show();
    //add the active class to sign-in link
    $(this).addClass("active");
});
</script>
<?php } ?>
</head>

<body class="<?php echo $this_body; ?> content">

	<div id="page-wrap">
        <div id="header">
            <h1><a href="<?php echo url::site() ?>mhi/">Home</a></h1>

            <ul class="primary-nav">
            	<?php mhitabs::main_tabs($this_body); ?>
            </ul>

            <?php if( ! is_int($mhi_user_id)) { ?>
            <div id="login-box">
                <p>Have an account?<a class="sign-in rounded" href="#">Sign In </a></p>
            </div>
            <?php }else{ ?>
           	<div id="login-box">
                <p><a href="<?php echo url::site() ?>mhi/manage" class="rounded">Manage Your Account</a> or <a href="<?php echo url::site() ?>mhi/logout" class="rounded" id="logout_link">Logout</a></p>
            </div>
            <?php } ?>

            <div id="login-form" class="rounded shadow">
                <?php print form::open(url::site().'mhi/', array('id' => 'frm-MHI-Login', 'name' => 'frm-Login')); ?>
                    <p>
                        <label for="username">Email</label>
                        <input type="text" name="username" class="text rounded" id="username" title="username" value="<?php echo $form['username'] ?>" />
                    </p>
                    <p>
                        <label for="password">Password</label>
                        <input type="password" name="password" class="text rounded" id="password" title="password" value="" />
                        <?php if($form_error === true) { ?>
                        	<div class="msg m-error-text"><?php echo $form_error_message; ?></div>
                        <?php } ?>
                    </p>
                    <p>
                        <input class="btn_sign-in rounded" type="submit" value="Sign In" />
                    </p>
                    <p class="forgot-password">
                        <a href="<?php echo url::site() ?>mhi/reset_password">Reset Password</a><br/>
                        <a href="<?php echo url::site() ?>mhi/signup">Create Account</a>
                    </p>
                <?php print form::close(); ?>
            </div>
        </div>

