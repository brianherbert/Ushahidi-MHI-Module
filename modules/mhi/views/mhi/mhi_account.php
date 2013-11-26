
		<div id="primary-content">
            <div class="twocol-left"><div class="content-shadow">
                <h2>Manage Your Account</h2>
				<div class="tabs">
                	<ul>
                    	<li><a class="" href="<?php echo url::site() ?>mhi/manage">Your Deployments</a></li>
                    	<li><a class="ab-active" href="<?php echo url::site() ?>mhi/account">Account Settings</a></li>
                    </ul>
                </div>
				<h3>Account Settings</h3>
				<?php if ($success_message != '') { ?>
					<div style="background-color:#95C274;border:4px #8CB063 solid;padding:2px 8px 1px 8px;margin:10px;"><?php echo $success_message; ?></div>
				<?php } ?>

				<?php print form::open(url::site().'mhi/account', array('id' => 'frm-MHI-Account', 'name' => 'frm-MHI-Account', 'class' => 'frm-content')); ?>

				<table><tbody>

					<?php if ($form_error) { ?>
			        <tr>
			          	<td align="left" class="error" colspan="2">
						<?php
						foreach ($errors as $error_item => $error_description)
						{
							echo '&#8226; '.$error_description.'<br />';
						}
						?>
						</td>
			        </tr>
					<?php } ?>

				    <tr>
				      <td><label for="firstname">First name</label></td>
				      <td><input type="text" size="24" name="firstname" maxlength="42" id="firstname" value="<?php echo $user->firstname; ?>" /></td>
				    </tr>
				    <tr>
				      <td><label for="lastname">Last name</label></td>
				      <td><input type="text" size="24" name="lastname" maxlength="42" id="lastname" value="<?php echo $user->lastname; ?>" /></td>
				    </tr>
				    <tr>
				      <td>&nbsp;</td>
				      <td><input class="button" type="submit" value="Update Account" /></td>
				    </tr>
				</tbody></table>

			<?php print form::close(); ?>

			<p>To <strong>change your password</strong>, please use our <a href="<?php echo url::site() ?>mhi/reset_password">reset password form</a>. You will be able to set a new password once you re-verify your email address.</p>


            </div></div>
            <div class="twocol-right">
                <p class="side-bar-buttons"><a class="admin-button green" href="<?php echo url::site() ?>mhi/signup">New Deployment</a></p>
            </div>
            <div style="clear:both;"></div>
        </div>