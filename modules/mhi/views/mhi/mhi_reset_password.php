
		<div id="primary-content">
            <div class="twocol-left"><div class="content-shadow">
                <h2>Password Reset</h2>
                <p class="intro-para">
                <?php
					if ($reset_success_flag == TRUE)
					{
				?>
						<p><strong>Password Reset!</strong></p>

						<p>You can now log in with your new password using the login box above.</p>
				<?php
					}
					elseif($show_pw_form_flag == TRUE)
					{
				?>
						<?php print form::open(url::site().'mhi/reset_password', array('id' => 'frm-MHI-Reset-Password', 'name' => 'frm-Reset-Password', 'class' => 'frm-content')); ?>
						<input type="hidden" name="token" value="<?php echo $token; ?>"/>
						<input type="hidden" name="email" value="<?php echo $email; ?>"/>
						<table><tbody>
							<tr>
								<td><label for="new_password">Email</label></td>
								<td><strong><?php echo $email; ?></strong></td>
							</tr>

							<?php if ($error_message != '') { ?>
							<tr>
								<td></td>
								<td style="color:red;"><?php echo $error_message; ?></td>
							</tr>
							<?php } ?>

							<tr>
								<td><label for="new_password">New Password</label></td>
								<td><input type="password" size="24" name="new_password" maxlength="32" id="new_password"/>
								<span>Use 5 to 32 characters.</span></td>
							</tr>
							<tr>
								<td><label for="verify_password">Verify Password</label></td>
								<td><input type="password" size="24" name="verify_password" maxlength="32" id="verify_password"/></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td><input class="button" type="submit" value="Set New Password" /></td>
							</tr>
						</tbody></table>
				        <?php print form::close(); ?>
				<?php
					}elseif($reset_email_sent_flag == TRUE && $email_exists){
				?>
						<p><strong>Email incoming!</strong></p>

						<p>We have sent you a link that you can use to reset your password. If you don't see an email from us in the next few minutes, please check your spam folder.</p>
				<?php
					}else{
				?>
						Enter your e-mail address and we will send you a message with link to reset your password.

						<?php print form::open(url::site().'mhi/reset_password', array('id' => 'frm-MHI-Reset-Password', 'name' => 'frm-Reset-Password', 'class' => 'frm-content')); ?>
		                <table><tbody>
							<tr>
								<td><label for="reset_email">Email</label></td>
								<td><input type="text" size="24" name="email" maxlength="42" id="email"/></td>
							</tr>

							<?php if ( ! $email_exists) { ?>
							<tr>
								<td></td>
								<td style="color:red;">Account doesn't exist. Please try again.</td>
							</tr>
							<?php } ?>

							<?php if ($error_message != '') { ?>
							<tr>
								<td></td>
								<td style="color:red;"><?php echo $error_message; ?></td>
							</tr>
							<?php } ?>

							<tr>
								<td>&nbsp;</td>
								<td><input class="button" type="submit" value="Reset Password" /></td>
							</tr>

						</tbody></table>
				        <?php print form::close(); ?>
				<?php
					}
                ?>
                </p>
            </div></div>
            <div style="clear:both;"></div>
        </div>