<?php
Event::run('ushahidi_action.mhi_manage_deployments');
?>

        <?php echo $google_goal; ?>

		<div id="primary-content">
            <div class="twocol-left"><div class="content-shadow">
                <h2>Manage Your Account</h2>

				<div class="tabs">
                	<ul>
                    	<li><a class="ab-active" href="<?php echo url::site() ?>mhi/manage">Your Deployments</a></li>
                    	<li><a class="" href="<?php echo url::site() ?>mhi/account">Account Settings</a></li>
                    </ul>
                </div>
				<h3>Your Deployments
                	<span id="deployment-filter" class="one-line-select">
                        <span id="site-filter-all" class="select-item selected first-child">All</span><span id="site-filter-active" class="select-item">Active</span><span id="site-filter-inactive" class="select-item last-child">Inactive</span>
                    </span>
                </h3>
                <p>View and manage your deployments.</p>

                <div id="deployments">
                <?php foreach($sites as $site) { ?>
                <div class="deployment <?php if($site->site_active == 1) { ?>d-active<?php }else{?>d-inactive<?php } ?> clearfix">
                	<div class="d-left">
                        <h4><a href="https://<?php echo $site->site_domain.'.'.$domain_name; ?>"><?php echo $site->site_name; ?></a>  <span><?php if($site->site_active == 1) { ?>active<?php }else{?>inactive<?php } ?></span></h4>
                        <p class="d-tagline"><?php echo $site->site_tagline; ?></p>

                    </div>
                    <div class="d-right">

                    </div>

                    <?php echo form::open(url::site().'mhi/manage', array('id' => 'frm-MHI-Admin-PW-Single', 'name' => 'frm-MHI-Admin-PW-Single', 'class' => 'frm-content')); ?>
                    <input type="hidden" name="site_domain" value="<?php echo $site->site_domain; ?>"/>
                    <p class="d-actions">
                    	<a target="_blank" href="https://<?php echo $site->site_domain.'.'.$domain_name; ?>admin">Admin Dashboard</a> |
						<?php if($site->site_active == 1) { ?> <a class="active-link" href="?deactivate=<?php echo $site->site_domain; ?>">Deactivate</a> <?php }else{ ?> <a class="active-link" href="?activate=<?php echo $site->site_domain; ?>">Activate</a> <?php } ?>
                	</p>
                	<?php echo form::close(); ?>

                </div>

				<?php } ?>

                <p class="no-results msg m-info">No results.</p>
                </div>

            </div></div>
            <div class="twocol-right">
                <p class="side-bar-buttons"><a class="admin-button green" href="<?php echo url::site() ?>mhi/signup">New Deployment</a></p>
            </div>
            <div style="clear:both;"></div>
        </div>