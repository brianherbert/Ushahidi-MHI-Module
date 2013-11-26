<?php defined('SYSPATH') or die('No direct script access.');

class MHI_Controller extends Template_Controller {

	// MHI template

	public $template = 'layout';

	public $first_map = FALSE;

	function __construct()
	{
		parent::__construct();

		// Load Header & Footer

		$this->template->header  = new View('mhi/mhi_header');
		$this->template->footer  = new View('mhi/mhi_footer');

		$this->template->footer->ushahidi_stats = Stats_Model::get_javascript();

		$this->template->header->site_name = Kohana::config('settings.site_name');

		// Initialize JS variables. js_files is an array of ex: html::script('media/js/jquery.validate.min');
		// Add the sign in box javascript

		$this->template->header->js = new View('mhi/mhi_js_signin');
		$this->template->header->js_files = array();

		// Get some information about our user

		$mhi_user = Mhi_User_Model::get_logged_in_user();

		// Login Form variables

		$this->template->header->errors = '';
		$this->template->header->form = array('username'=>'');
		$this->template->header->form_error = '';
		$this->template->header->mhi_user_id = (isset($mhi_user->id)) ? $mhi_user->id : '';
		$this->template->header->mhi_user_email = (isset($mhi_user->email)) ? $mhi_user->email : '';

		Event::add('ushahidi_action.mhi_deployment_created', array($this, '_mhi_deployment_created'));
		Event::add('ushahidi_action.mhi_manage_deployments', array($this, '_mhi_manage_deployments'));
	}

	// Save a cookie about the deployment we just created.
	public function _mhi_deployment_created() {
		$deployment = Event::$data;

		cookie::set(array(
			'name' => 'event_created_deployment',
			'value' => $deployment,
			'domain' => '.' . $_SERVER['HTTP_HOST'],
			'expires' => 0,
			'path' => '/',
			'secure' => TRUE,
			'httponly' => TRUE
		));
	}

	// Check for the "we just created a deployment" cookie
	public function _mhi_manage_deployments() {
		$session = new Session;
		$session = Session::instance();

		if($deployment = cookie::get('event_created_deployment', FALSE)) {
			$deployment = 'https://' . $deployment . '.' . $_SERVER['HTTP_HOST'];
			cookie::delete('event_created_deployment');
		}
	}

	public function index()
	{
		$this->template->header->this_body = 'crowdmap-home';

		$this->template->content = new View('mhi/mhi');

		$this->template->header->js .= new View('mhi/mhi_js');
		$this->template->header->js_files = array(html::script('media/js/mhi/jquery.cycle.min'));

		// Get some information about our logged in user
		$mhi_user = Mhi_User_Model::get_logged_in_user();
		$mhi_user_id = (isset($mhi_user->id)) ? $mhi_user->id : FALSE;

		$form = array(
			'username' => '',
			'password' => '',
			);

		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names

		$errors = $form;
		$form_error = FALSE;
		$form_error_message = 'Invalid login. Please try again.';

		// Set up the validation object

		$_POST = Validation::factory($_POST)
			->pre_filter('trim')
			->add_rules('username', 'required')
			->add_rules('password', 'required');

		// OR $mhi_user_id != FALSE
		if ($_POST->validate())
		{
			// Sanitize $_POST data removing all inputs without rules

			$postdata_array = $_POST->safe_array();

			// MHI user not already logged in, so do it

			if ($mhi_user_id == FALSE)
			{
				try {
					$mhi_user_id = Mhi_User_Model::login($postdata_array['username'],$postdata_array['password']);
				} catch (Exception $e) {
					$form_error_message = $e->getMessage();
				}
			}

			// If success (already logged in or login successful), move on

			if ($mhi_user_id != FALSE)
			{

				url::redirect('mhi/manage');

			}else{

				$_POST->add_error('username', 'Login Error');

				// Repopulate the form fields

				$form = arr::overwrite($form, $_POST->as_array());

				// Populate the error fields, if any
				// We need to already have created an error message file, for Kohana to use
				// Pass the error message file name to the errors() method

				$errors = arr::overwrite($errors, $_POST->errors('auth'));
				$form_error = TRUE;

			}
		}

		$this->template->header->errors = $errors;
		$this->template->header->form = $form;
		$this->template->header->form_error = $form_error;
		$this->template->header->form_error_message = $form_error_message;
	}

	public function manage($horray=FALSE)
	{

		// If not logged in, go back to the start
		// Get some information about our logged in user
		$mhi_user = Mhi_User_Model::get_logged_in_user();
		$mhi_user_id = (isset($mhi_user->id)) ? $mhi_user->id : FALSE;

		if ($mhi_user_id == FALSE)
		{
			// If the user is not logged in, go home.

			url::redirect('/');
		}

		// Activate or deactivate a site

		if(isset($_GET['deactivate']) OR isset($_GET['activate']))
		{
			$this->activation();
		}

		$this->template->header->this_body = '';
		$this->template->content = new View('mhi/mhi_manage');
		$this->template->content->sites_pw_changed = array();

		// Manage JS

		$this->template->header->js .= new View('mhi/mhi_manage_js');

		$this->template->content->domain_name = $_SERVER['HTTP_HOST'].Kohana::config('config.site_domain');

		$mhi_site = new Mhi_Site_Model;
		$all_user_sites = $mhi_site->get_user_sites($mhi_user_id,TRUE);
		$this->template->content->sites = $all_user_sites;

		$this->template->content->google_goal = '';
	}

	public function activation()
	{

		if( ! isset($_GET['deactivate']) AND ! isset($_GET['activate'])) return false;

		// Get some information about our logged in user
		$mhi_user = Mhi_User_Model::get_logged_in_user();
		$mhi_user_id = (isset($mhi_user->id)) ? $mhi_user->id : FALSE;

		if(isset($_GET['deactivate']))
		{
			$site_domain = $_GET['deactivate'];
			$activation = 0;
		}else{
			$site_domain = $_GET['activate'];
			$activation = 1;
		}

		$mhi_site = new Mhi_Site_Model;

		// Check if the logged in user is the owner of the site

		$domain_owners = $mhi_site->domain_owner(array($site_domain));

		// using array_unique to see if there is only one owner

		$domain_owners = array_unique($domain_owners);

		if(count($domain_owners) != 1)
		{
			// If there are more than one owner, the we shouldn't be able to change all those passwords.
			throw new Kohana_User_Exception('Site Ownership Error', "Improper owner for site to change password.");
		}

		$domain_owner = current($domain_owners);

		// If the owner of the site isn't the person updating the password for the site, there's something fishy going on

		if($domain_owner == $mhi_user_id)
		{
			$mhi_site->activation($site_domain,$activation);
		}
	}

	public function legal($page='tos')
	{
		return;
	}

	public function contact()
	{
		$this->template->header->this_body = 'crowdmap-contact';
		$this->template->content = new View('mhi/mhi_contact');

        $form = array(
            'contact_email' => '',
            'contact_subject' => '',
            'contact_message' => '',
            'contact_captcha' => '',
        );

		$errors = $form;

        $success_message = '';
        $form_error = FALSE;
        $captcha = Captcha::factory();

		if ($_POST)
		{
			$post = Validation::factory($_POST)
				->pre_filter('trim')
				->add_rules('contact_email', 'required', array('valid','email'))
				->add_rules('contact_subject', 'required')
				->add_rules('contact_message', 'required')
                ->add_rules('contact_captcha', 'required','Captcha::valid');

			if ($post->validate())
			{

				email::send(Kohana::config('settings.site_email'),$post->contact_email,$post->contact_subject,$post->contact_message,FALSE);

				$success_message = 'Email sent. We will get back to you as quickly as we can. Thank you!';

			}
            else
            {

                $form = arr::overwrite($form, $post->as_array());

                $errors = arr::overwrite( $errors,
                        $post->errors('mhi'));
                $form_error = TRUE;

			}

		}

        $this->template->content->form = $form;
        $this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;
		$this->template->content->success_message = $success_message;
        $this->template->content->captcha = $captcha;
	}

	// Displays true if the email is free to be registered
	public function checkemail()
	{
		if (isset($_REQUEST['signup_email'])) {
			$email = $_REQUEST['signup_email'];

			$this->template->header = FALSE;
			$this->template->footer = FALSE;
			$this->template->content = FALSE;

			$id = Mhi_User_Model::get_id($email);
			if($id == NULL OR $id == FALSE OR $id == '')
			{
				// Didn't find it in the DB, try checking on the CrowdmapID server
				$riverid = new RiverID;
				$riverid->email = $email;
				$is_registered = $riverid->is_registered();

				if ($is_registered != TRUE)
				{
					echo 'true';
					exit;
				}
			}
		}

		echo 'false';
		exit;
	}

	// Displays true if the email is free to be registered
	public function checksubdomain()
	{
		$this->template->header = FALSE;
		$this->template->footer = FALSE;
		$this->template->content = FALSE;

		// Check for the domain existing on Classic Crowdmap
		$exists = Mhi_Site_Model::domain_exists($_REQUEST['signup_subdomain']);
		if($exists == TRUE)
		{
			// Exists on classic crowdmap
			echo 'false';
			return false;
		}

		// Doesn't exist anywhere yet.
		echo 'true';
		return true;
	}

	public function page($page_id=1)
	{
		$this->template->header->this_body = "page_".$page_id;
        $this->template->content = new View('mhi_page');

        if ( ! $page_id)
        {
            url::redirect('mhi');
        }

        $page = ORM::factory('page',$page_id)->find($page_id);
        if ($page->loaded)
        {
            $this->template->content->page_description = $page->page_description;
        }
        else
        {
            url::redirect('mhi');
        }
	}

	public function account()
	{

		// If not logged in, go back to the start

		// Get some information about our logged in user
		$mhi_user = Mhi_User_Model::get_logged_in_user();
		$mhi_user_id = (isset($mhi_user->id)) ? $mhi_user->id : FALSE;

		if ($mhi_user_id == FALSE)
		{
			// If the user is not logged in, go home.
			url::redirect('/');
		}

		$this->template->header->this_body = '';
		$this->template->content = new View('mhi/mhi_account');
		$this->template->header->js .= new View('mhi/mhi_account_js');

		// Initiate the variable that holds the message displayed on form success

		$this->template->content->success_message = '';

		$mhi_user = new Mhi_User_Model;

		// Get user info

		$this->template->content->user = $mhi_user->get($mhi_user_id);

		$form_error = FALSE;
		$errors = FALSE;

		// Set up the validation object

		$_POST = Validation::factory($_POST)->pre_filter('trim');

		if ($_POST->validate())
		{
			$mhi_user = new Mhi_User_Model;

			$postdata_array = $_POST->safe_array();

			$firstname = '';
			$lastname = '';

			if (isset($postdata_array['firstname']))
			{
				$firstname = $postdata_array['firstname'];
			}

			if (isset($postdata_array['lastname']))
			{
				$lastname = $postdata_array['lastname'];
			}

			$update = $mhi_user->update($mhi_user_id,array(
				'firstname'=>$firstname,
				'lastname'=>$lastname
			));

			// If update worked, present a success message to the user

			if ($update != FALSE)
			{
				$this->template->content->success_message = 'Success! You have updated your account.';

				// Reload user information since it has changed

				$this->template->content->user = $mhi_user->get($mhi_user_id);

			}else{
				$errors = array('Something went wrong with form submission. Please try again.');
				$form_error = TRUE;
			}
		}

		$this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;

	}

	public function logout()
	{
		Mhi_User_Model::logout();

		url::redirect('/');
	}

	public function reset_password()
	{
		$this->template->header->this_body = '';
		$this->template->content = new View('mhi/mhi_reset_password');
		$this->template->content->show_pw_form_flag = FALSE;
		$this->template->content->reset_email_sent_flag = FALSE;
		$this->template->content->reset_success_flag = FALSE;
		$this->template->content->email_exists = TRUE;
		$this->template->content->error_message = '';
		$this->template->content->token = FALSE;
		$this->template->content->email = FALSE;

		// When the user clicks the email to come back to reset their password, we will convert
		//   the GET vars to POST for the Kohana validation library. We need to know that this
		//   did not come from a form.
		$post_from_form = TRUE;

		// The Validation library strips _GET vars on fail so convert email and token to post vars
		if ( isset($_GET['e']) AND isset($_GET['t']))
		{
			$_POST['email'] = $_GET['e'];
			$_POST['token'] = $_GET['t'];
			$post_from_form = FALSE;
		}

		if ( isset($_POST['email']) AND isset($_POST['token'])) // e = base64 encoded email, t = token
		{
			$this->template->content->token = $_POST['token'];
			$this->template->content->email = base64_decode($_POST['email'],true);
			$this->template->content->show_pw_form_flag = TRUE;

			// Check if the email was decoded properly, if not, it wasn't encoded
			if ($this->template->content->email == FALSE)
			{
				$this->template->content->email = $_POST['email'];
			}
		}

		if ($_POST AND $post_from_form)
		{
			// Validate the email address
			$post = Validation::factory($_POST);
			$post->pre_filter('trim');
			$post->add_rules('email', 'required','email');

			// We need additional rules if we are setting the password and not
			//   just sending the email to ask the user to provide a new password
			if ( isset($post->new_password))
			{
				// Also, keep the user on the new password form if validation fails
				$this->template->content->show_pw_form_flag = TRUE;

				$post->add_rules('new_password', 'required', 'matches[verify_password]');
				$post->add_rules('token', 'required');
			}

			if ($post->validate())
			{
				$email = $post->email;

				// If we are doing the reset of the password
				if ($this->template->content->show_pw_form_flag == TRUE)
				{
					// Passed validation, reset the users password!
					$riverid = new RiverID;
					$riverid->email = $email;
					$riverid->new_password = $post->new_password;
					$riverid->token = $post->token;
					if ($riverid->setpassword() != FALSE)
					{
						$this->template->content->reset_success_flag = TRUE;
					}
					else
					{
						// If something went wrong on the RiverID server
						$this->template->content->error_message = $riverid->error[0];
						$this->template->content->show_pw_form_flag == TRUE;
					}
				}
				else
				{
					// If we need to send the email for the user to come back and reset their password

					$secret_link = url::site('mhi/reset_password/?e='.base64_encode($email).'&t=%token%');
					$message = "Hello!\n\nCrowdmap received a request to reset your CrowdmapID password. To change your password, please click on the link below (or copy and paste it into your browser). If you did not initiate this request, you can ignore it and your account will remain unchanged.\n\n".$secret_link."\n\nThank You,\n\nThe Crowdmap Team";

					$riverid = new RiverID;
					$riverid->email = $email;
					$riverid->requestpassword($message);

					$this->template->content->reset_email_sent_flag = TRUE;
				}
			}
			else
			{
				$this->template->content->error_message = 'Please use a valid email address.';
			}
		}
	}

	public function signup()
	{
		$this->template->header->this_body = '';
		$this->template->content = new View('mhi/mhi_signup');
		$this->template->header->js .= new View('mhi/mhi_signup_js');
		$this->template->header->js_files = array(html::script('media/js/mhi/initialize', true));

		$this->template->content->site_name = Kohana::config('settings.site_name');
		$this->template->content->domain_name = $_SERVER['HTTP_HOST'].Kohana::config('config.site_domain');

		$mhi_user = Mhi_User_Model::get_logged_in_user();
		$this->template->content->logged_in = (isset($mhi_user->id)) ? $mhi_user->id : FALSE;

		$form_array = array(
			'errors' => array(),
			'form' => array(
				'signup_first_name' => '',
				'signup_last_name' => '',
				'signup_email' => '',
				'signup_password' => '',
				'signup_subdomain' => '',
				'signup_instance_name' => '',
				'signup_instance_tagline' => ''
			),
			'form_error' => array()
		);

		if ($_POST)
		{
			$form_array = $this->processcreation();

			// If there were no errors, redirect to management page

			if(count($form_array['form_error']) == 0)
			{
				Event::run('ushahidi_action.mhi_deployment_created', $form_array['form']['signup_subdomain']);
				if ($this->first_map)
				{
					// If this is the first map the user has set up on this account...
					$redirect_to = 'mhi/manage/horray';
				}
				else
				{
					$redirect_to = 'mhi/manage';
				}
				url::redirect($redirect_to);
			}

		}

		$this->template->content->errors = $form_array['errors'];
		$this->template->content->form = $form_array['form'];
		$this->template->content->form_error = $form_array['form_error'];
	}

	public function processcreation()
	{
		// Used to populate form fields. Will assign values on error

		$errors = array();
		$form = array(
			'signup_first_name' => '',
			'signup_last_name' => '',
			'signup_email' => '',
			'signup_password' => '',
			'signup_subdomain' => '',
			'signup_instance_name' => '',
			'signup_instance_tagline' => ''
		);
		$form_error = array();

		// Process Form

		if ($_POST)
		{

			$sfn = isset($_POST['signup_first_name']) ? $_POST['signup_first_name'] : '';
			$sln = isset($_POST['signup_last_name']) ? $_POST['signup_last_name'] : '';
			$sem = isset($_POST['signup_email']) ? $_POST['signup_email'] : '';
			$spw = isset($_POST['signup_password']) ? $_POST['signup_password'] : '';

			$form = array(
				'signup_first_name' => $sfn,
				'signup_last_name' => $sln,
				'signup_email' => $sem,
				'signup_password' => $spw,
				'signup_subdomain' => strtolower($_POST['signup_subdomain']),
				'signup_instance_name' => $_POST['signup_instance_name'],
				'signup_instance_tagline' => $_POST['signup_instance_tagline']
			);


			$post = Validation::factory($_POST);

			// Trim whitespaces

			$post->pre_filter('trim');

			$mhi_user = Mhi_User_Model::get_logged_in_user();
			$mhi_user_id = (isset($mhi_user->id)) ? $mhi_user->id : FALSE;

			$blocked_subdomains = Kohana::config('mhi.blocked_subdomains');

			// These rules are only required if we aren't already logged in

			if ($mhi_user_id == FALSE)
			{
				$post->add_rules('signup_first_name','required');
				$post->add_rules('signup_last_name','required');
				$post->add_rules('signup_email', 'required','email');
				$post->add_rules('signup_password','required');
			}

			$post->add_rules('signup_subdomain','required','alpha_numeric');
			$post->add_rules('signup_instance_name','required');
			$post->add_rules('signup_instance_tagline','required');

			// If we pass validation AND it's not one of the blocked subdomains
			if ($post->validate())
			{

				$mhi_user = new Mhi_User_Model;
				$db_genesis = new DBGenesis;
				$mhi_site_database = new Mhi_Site_Database_Model;
				$mhi_site = new Mhi_Site_Model;

				// Setup DB name variable

				$base_db = $db_genesis->current_db();

				$new_db_name = $base_db.'_'.strtolower($post->signup_subdomain);

				// Do some graceful validation

				if ( ! isset($post->signup_tos))
				{
					return array(
						'errors' => $errors,
						'form' => $form,
						'form_error' => array('signup_tos' => 'You must accept the Website Terms of Use.')
					);
				}

				if (strlen($post->signup_subdomain) < 4 OR strlen($post->signup_subdomain) > 32)
				{
					// ERROR: subdomain length falls outside the char length bounds allowed.

					return array(
						'errors' => $errors,
						'form' => $form,
						'form_error' => array('signup_subdomain' => 'Subdomain must be between at least 4 characters and no more than 32 characters long. Please try again.')
					);
				}

				if ($mhi_site->domain_exists($post->signup_subdomain))
				{
					// ERROR: Domain already assigned in MHI DB.

					return array(
						'errors' => $errors,
						'form' => $form,
						'form_error' => array('signup_subdomain' => 'This subdomain has already been taken. Please try again.')
					);
				}

				if ($mhi_site_database->db_assigned($new_db_name) OR $db_genesis->db_exists($new_db_name))
				{
					// ERROR: Database already exists and/or is already assigned in the MHI DB

					return array(
						'errors' => $errors,
						'form' => $form,
						'form_error' => array('signup_subdomain' => 'This subdomain is not allowed. Please try again.')
					);
				}

				if(in_array(strtolower($post->signup_subdomain),$blocked_subdomains))
				{
					// ERROR: Blocked Subdomain

					return array(
						'errors' => $errors,
						'form' => $form,
						'form_error' => array('signup_subdomain' => 'This subdomain is not allowed. Please try again.')
					);
				}

				// Check passwords if logged in and create user if not

				// If logged in
				if ($mhi_user_id != FALSE)
				{

					// Get user info

					$user = $mhi_user->get($mhi_user_id);

					$user_id = $mhi_user_id;
					$email = $user->email;
					$name = $user->firstname.' '.$user->lastname;

				// If not logged in
				}else{

					// Save new user

					$mailinglist = 0;
					if( isset($post->signup_mailinglist) )
					{
						$mailinglist = 1;
					}

					$skip_login = TRUE; // We need to forge on and create some DBs first so skip login here
					$user_id = $mhi_user->save_user(array(
						'firstname'=>$post->signup_first_name,
						'lastname'=>$post->signup_last_name,
						'email'=>$post->signup_email,
						'password'=>$post->signup_password,
						'mailinglist'=>$mailinglist
					), $skip_login);

					$email = $post->signup_email;
					$name = $post->signup_first_name.' '.$post->signup_last_name;
					$password = $post->signup_password;

					// Log new user in
					$redirect = FALSE; // We need to forge on and create some DBs first
					$mhi_user_id = $mhi_user->login($email,$password,$redirect);

				}

				// Set up DB and Site

				// Create site

				$site_id = $mhi_site->save_site(array(
					'user_id'=>$user_id,
					'site_domain'=>strtolower($post->signup_subdomain),
					'site_privacy'=>1,	// TODO: 1 is the hardcoded default for now. Needs to be changed?
					'site_active'=>1	// TODO: 1 is the default. This needs to be a config item since this essentially "auto-approves" sites
				));

				// Set up database and save details to MHI DB

				$db_genesis->create_db($new_db_name);
				$mhi_site_database->assign_db($new_db_name,$site_id);
				$db_genesis->populate_db($new_db_name,
					array(
						'username'=>$email,
						'name'=>$name,
						'email'=>$email,
						'riverid'=>Mhi_User_Model::get_riverid($user_id)),
					array(
						'site_name'=>$post->signup_instance_name,
						'site_tagline'=>$post->signup_instance_tagline,
						'site_domain'=>strtolower($post->signup_subdomain)));

				// Congrats, everything has been set up. Send an email confirmation.

				$settings = kohana::config('settings');
				$new_site_url = 'https://'.strtolower($post->signup_subdomain).'.'.$_SERVER['HTTP_HOST'].Kohana::config('config.site_domain');

				if ($settings['site_email'] != NULL)
				{
					$to = $email;
					$from = $settings['site_email'];
					$subject = 'Your deployment at '.$settings['site_name'];
					$message = 'Your new site, '.$post->signup_instance_name.' has been set up.'."\n";
					$message .= 'Admin URL: '.$new_site_url.'admin'."\n";
					$message .= 'Username: '.$email."\n";
					$message .= 'Password: (hidden)'."\n";

					email::send($to,$from,$subject,$message,FALSE);
				}

				// Check if this is the first map for the user so we can track goals properly
				if ($mhi_site->count_user_sites($user_id) == 1)
				{
					$this->first_map = TRUE;
				}
				else
				{
					$this->first_map = FALSE;
				}

			}else{
				if (isset($_POST['signup_password'])) unset($_POST['signup_password']);
				if (isset($_POST['signup_confirm_password'])) unset($_POST['signup_confirm_password']);
				if (isset($_POST['verify_password'])) unset($_POST['verify_password']);
				throw new Kohana_User_Exception('Validation Error', "Form not validating. Please go back and try again.");
			}

		}else{

			// If the form was never posted, we need to complain about it.

			throw new Kohana_User_Exception('Incomplete Form', "Form not posted.");
		}

		$query = 'SELECT `site_domain` FROM `mhi_site`';
		$result = mysql_query($query);
		$list = '';
		while ($row = mysql_fetch_assoc($result))
		{
			$list .= $row['site_domain']."\n";
		}

		$file = 'mhi_extras/registered_list/list';
		$fh = fopen($file, 'w') or die("can't open file");
		fwrite($fh, $list);
		fclose($fh);


		return array(
			'errors' => $errors,
			'form' => $form,
			'form_error' => $form_error
		);
	}

}
