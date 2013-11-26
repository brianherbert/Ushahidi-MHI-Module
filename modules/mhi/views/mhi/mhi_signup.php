
        <div id="primary-content">
            <div class="twocol-left"><div class="content-shadow">

            	<?php if($logged_in == FALSE){ ?>

            		<h2>Sign up for your free Crowdmap account</h2>
                	<p>Fill out the form below to set up your own Crowdmap.</p>

                	<?php print form::open(url::site().'mhi/signup', array('id' => 'frm-MHI-Signup', 'name' => 'frm-MHI-Signup', 'class' => 'frm-content')); ?>

                    <h2>Create Your Account</h2>

                    <table><tbody>
			            <tr>
			              <td><label for="signup_first_name">First name</label></td>
			              <td><input type="text" size="24" name="signup_first_name" maxlength="42" id="signup_first_name" value="<?php echo $form['signup_first_name']; ?>"/></td>
			            </tr>
			            <tr>
			              <td><label for="signup_last_name">Last name</label></td>
			              <td><input type="text" size="24" name="signup_last_name" maxlength="42" id="signup_last_name" value="<?php echo $form['signup_last_name']; ?>"/></td>
			            </tr>
			            <tr>
			              <td><label for="signup_email">Email</label></td>
			              <td><input type="text" size="24" name="signup_email" maxlength="42" id="signup_email" value="<?php echo $form['signup_email']; ?>"/>
							<span>This will also be your username.</span>
							<div style="line-height:20px;display:none;" id="already_registered_info">
								<p><strong>It looks like your username is already registered!</strong><br/>
								Please try logging in above.</p>
								<p>Enter this email address by mistake? <a href="<?php echo url::site()."mhi/signup" ?>">Try again</a>.</p>
							</div>
						</td>
			            </tr>
			            <tr>
			              <td><label for="signup_password">Password</label></td>
			              <td><input type="password" size="24" name="signup_password" maxlength="42" id="signup_password" value="<?php echo $form['signup_password']; ?>"/>
			              <span>Use 5 to 32 characters.</span></td>
			            </tr>
			            <tr>
			              <td><label for="signup_confirm_password">Confirm Password</label></td>
			              <td><input type="password" size="24" name="signup_confirm_password" maxlength="42" id="signup_confirm_password"/></td>
			            </tr>

			            <?php if(isset($form_error['password'])) { ?>
			            <tr>
			            	<td></td>
			            	<td><p style="color:red">* <?php echo $form_error['password']; ?></p></td>
			            </tr>
	                    <?php } ?>

			        </tbody></table>

			        <hr/>

			        <?php }else{ ?>

			        <h2>Create a new map</h2>

                	<?php print form::open(url::site().'mhi/signup', array('id' => 'frm-MHI-Signup', 'name' => 'frm-MHI-Signup', 'class' => 'frm-content')); ?>

			        <?php } ?>

                    <h2>Create Your Map Address</h2>
                    <p class="desc">Each map has it's own web address. No spaces, use letters and numbers only.<br/><strong>This is permanent and cannot be changed.</strong></p>
       				<p class="url">http://<input type="text" size="20" onfocus="this.style.color = 'black'" name="signup_subdomain" maxwidth="30" id="signup_subdomain" value="<?php echo $form['signup_subdomain']; ?>"/>.<?php echo $domain_name; ?></p>

       				<?php if(isset($form_error['signup_subdomain'])) { ?>
       				<p style="color:red">* <?php echo $form_error['signup_subdomain']; ?></p>
                    <?php } ?>

                    <hr />

                    <h2>Enter Your Map Details</h2>
                    <p>
			        	<label for="signup_instance_name">Map Name</label><br/>
			        	<input type="text" size="30" name="signup_instance_name" maxlength="100" id="signup_instance_name" value="<?php echo $form['signup_instance_name']; ?>" autocomplete="off"/>
			        </p>
			        <p>
			        	<label for="signup_instance_tagline">Map Tagline</label><br/>
			        	<input type="text" size="30" name="signup_instance_tagline" maxlength="100" id="signup_instance_tagline" value="<?php echo $form['signup_instance_tagline']; ?>" autocomplete="off"/>
			        </p>

			        <h2>Accept Terms</h2>
			        <p>
			        	<input type="checkbox" name="signup_tos" id="signup_tos" value="1" /> <label for="signup_tos">I have read and agree</label> to the <a href="<?php echo url::site(); ?>mhi/legal" target="_blank">Website Terms of Use</a>
			        </p>

			        <?php if($logged_in == FALSE){ ?>
			        <p>
			        	<input type="checkbox" name="signup_mailinglist" id="signup_mailinglist" value="1" /> <label for="signup_mailinglist">I am open to receiving very occasional emails relating to Crowdmap.</label>
			        	<span>We hate spam as much as you do. Since we launched Crowdmap, we have sent 1 email to our mailing list.</span>
			        </p>
			        <?php } ?>

			        <p>
			        	<input class="button" type="submit" value="Finish &amp; Create Map" />
			        </p>

            <?php print form::close(); ?>

            </div></div>
            <div class="twocol-right">

      				<!-- right nav -->
      				<div class="side-bar-module rounded shadow" style="margin-top:410px">
      					<h4>Right side column</h4>
      					<div class="side-bar-content">
      						Hi.
      					</div>
      				</div>
      				<!-- / right nav -->

            </div>
            <div style="clear:both;"></div>
        </div>









