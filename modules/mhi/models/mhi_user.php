<?php defined('SYSPATH') or die('No direct script access.');


class Mhi_User_Model extends ORM
{
	protected $table_name = 'mhi_users';

	protected $primary_key = 'id';

	protected $primary_val = 'email';

	// $a should be an assoc array including riverid (128 char id from riverid server), email, firstname, lastname (plain text) and mailinglist (0,1)
	// $skiplogin set to true if you want to skip authenticating the user

	static function save_user($a, $skiplogin=FALSE)
	{
		$salt = Kohana::config('auth.salt_pattern');

		$mailinglist = 0;
		if(isset($a['mailinglist']) AND $a['mailinglist'] == 1)
		{
			$mailinglist = 1;
		}

		$riverid = '';
		if(isset($a['riverid']))
		{
			$riverid = $a['riverid'];
		}

		$firstname = '';
		if(isset($a['firstname']))
		{
			$firstname = $a['firstname'];
		}

		$lastname = '';
		if(isset($a['lastname']))
		{
			$lastname = $a['lastname'];
		}

		// First see if the user already exists for some reason before moving on
		$mhi_user_check = ORM::factory('mhi_user')->where('email',$a['email'])->find_all();
		foreach($mhi_user_check as $check)
		{
			// User exists in the db, attach the river id
			Mhi_User_Model::attach_riverid($check->id,$riverid);
			return $check->id;
		}


		$mhi_user = ORM::factory('mhi_user');
		$mhi_user->riverid = $riverid;
		$mhi_user->firstname = $firstname;
		$mhi_user->lastname = $lastname;
		$mhi_user->email = $a['email'];
		$mhi_user->mailinglist = $mailinglist;

		// Still need to save password. This is used when creating a user the
		//   first time. The login process is what links their RiverID account.
		if ( isset($a['password']))
		{
				$salt = Kohana::config('auth.salt_pattern');
				$password = sha1($a['password'].$salt);
				$mhi_user->password = $password;
		}

		$mhi_user->save();

		Event::run('ushahidi_action.mhi_user_created', $a['email']);

		if ( ! $skiplogin )
		{
			// Log the new user in so they will be authenticated after creation
			Mhi_User_Model::login($a['email'],$a['password']);
		}

		$result = ORM::factory('mhi_user')->where('email',$a['email'])->find_all();
		$id = 0;
		foreach ($result as $res)
			$id = $res->id;

		return $id;
	}

	static function attach_riverid($userid,$riverid)
	{
		$mhi_user = ORM::factory('mhi_user',$userid);
		$mhi_user->riverid = $riverid;
		$mhi_user->save();
		return TRUE;
	}

	// This function is for logging in MHI users, NOT Ushahidi admin users!
	//  Take plaintext email, plaintext password and an unrequired bool to
	//  determine if the user should be redirected on success

	static function login($email,$password,$redirect=TRUE)
	{
		$riverid = new RiverID;
		$riverid->email = $email;
		$riverid->password = $password;
		$is_registered = $riverid->is_registered();

		// If there's an error, drop out.
		if ($riverid->error)
		{
			throw new Exception($riverid->error[0]);
		}

		if($is_registered == true)
		{
			// RiverID is registered on RiverID Server

			// Attempt to sign in
			$riverid->signin();

			if($riverid->authenticated == true)
			{
				// Correct email/pass

				// Collect the RiverID user_id and connect that with a user in the local system

				$user = Mhi_User_Model::get_user_by_riverid($riverid->user_id);

				if ( ! $user->id)
				{
					// This user doesn't exist locally so create an account for them.

					// Does this user already have an account?
					$correct_user = Mhi_User_Model::correct_local_email_pass($riverid->email,$riverid->password);
					if ($correct_user)
					{
						// Yes, this user exists, attach RiverID
						Mhi_User_Model::attach_riverid($correct_user,$riverid->user_id);
					}
					else
					{
						// No user yet. The user creation step will return here to log them in
						Mhi_User_Model::save_user(array('email'=>$riverid->email,'riverid'=>$riverid->user_id),TRUE);
					}
				}

				// Now that we have our user account tied to their RiverID, approve their authentication

				Mhi_User_Model::perform_login($riverid);

				if ($redirect == TRUE)
				{
					url::redirect('mhi/manage/');
				}

				return TRUE;
			}
			else
			{
				// Incorrect email/pass, but registered on RiverID. Failed login.

				if ($riverid->error)
				{
					throw new Exception($riverid->error[0]);
				}

				return FALSE;
			}
		}
		else
		{
			// Email is not registerd on RiverID Server, could be registered locally

			// First see if they used the correct user/pass on their local account

			$mhi_user = Mhi_User_Model::get_user_by_email($riverid->email);

			if ( ! $mhi_user->id)
			{
				// User doesn't exist locally or on RiverID. Fail login.

				if ($riverid->error)
				{
					throw new Exception($riverid->error[0]);
				}

				return FALSE;
			}
			else
			{

				// User exists locally but doesn't yet exist on the RiverID server

				// Check if they got the password correct

				$correct_user = Mhi_User_Model::correct_local_email_pass($riverid->email,$riverid->password);

				if ($correct_user)
				{
					// Correct password! Create RiverID account
					$riverid->register();

					// If something went wrong with registration, catch it here
					if ($riverid->error)
					{
						throw new Exception($riverid->error[0]);
					}

					// Our user is now registered, let's assign the riverid user to the db.
					Mhi_User_Model::attach_riverid($correct_user,$riverid->user_id);

					// Now lets sign them in
					$riverid->signin();

					// If something went wrong with signin, catch it here
					if ($riverid->error)
					{
						throw new Exception($riverid->error[0]);
					}

					Mhi_User_Model::perform_login($riverid);

					if ($redirect == TRUE)
					{
						url::redirect('mhi/manage/');
					}

					return TRUE;

				}
				else
				{
					// Incorrect user/pass. Fail login.

					if ($riverid->error)
					{
						throw new Exception($riverid->error[0]);
					}

					return FALSE;
				}
			}
		}

		// Everything should have happened above but in case something went bonkers, fail the login

		return FALSE;
	}

	// Save the riverid object as our login piece
	static function perform_login($riverid)
	{
		$session = Session::instance();
		$session->set('riverid',$riverid);
	}


	static function old_login($email,$password)
	{
		$salt = Kohana::config('auth.salt_pattern');
		$password = sha1($password.$salt);
		$result = ORM::factory('mhi_user')->where('email',$email)->where('password',$password)->find_all();
		$id = FALSE;

		foreach ($result as $res)
			$id = $res->id;

		$session = Session::instance();
		$session->set('mhi_user_id',$id);

		return $id;
	}

	function correct_local_email_pass($email,$password)
	{
		$salt = Kohana::config('auth.salt_pattern');
		$password = sha1($password.$salt);
		$result = ORM::factory('mhi_user')->where('email',$email)->where('password',$password)->find_all();

		$id = FALSE;
		foreach ($result as $res)
			$id = $res->id;

		return $id;
	}

	// No BS. Doesn't take any arguments.

	static function logout()
	{
		Session::destroy();
	}

	// Get user details

	static function get($user_id)
	{
		$result = ORM::factory('mhi_user')->where('id',$user_id)->find_all();
		$details = FALSE;
		foreach ($result as $res)
			return $res;
	}

	// Get list of all users in an array

	static function get_all_users()
	{
		$result = ORM::factory('mhi_user')->find_all();
		$array = array();
		foreach ($result as $res)
		{
			$array[$res->id]['email'] = $res->email;
			$array[$res->id]['firstname'] = $res->firstname;
			$array[$res->id]['lastname'] = $res->lastname;
		}
		return $array;
	}

	static function get_id($email)
	{
		$result = ORM::factory('mhi_user')->where('email',$email)->find_all();
		foreach ($result as $res)
			return $res->id;

		return FALSE;
	}

	static function get_riverid($id)
	{
		$result = ORM::factory('mhi_user',$id)->find_all();
		foreach ($result as $res)
		{
			return $res->riverid;
		}

		return FALSE;
	}

	static function get_email($id)
	{
		$result = ORM::factory('mhi_user',$id)->find_all();
		foreach ($result as $res)
			return $res->email;

		return FALSE;
	}

	static function get_user_by_riverid($riverid)
	{
		return ORM::factory('mhi_user')->where(array('riverid'=>$riverid))->find();
	}

	static function get_user_by_mhi_user_id($id)
	{
		return ORM::factory('mhi_user',$id)->find();
	}

	static function get_user_by_email($email)
	{
		return ORM::factory('mhi_user')->where(array('email'=>$email))->find();
	}

	static function get_logged_in_user()
	{
		$session = Session::instance();
		$riverid = $session->get('riverid');

		if ( isset($riverid->authenticated) AND $riverid->authenticated == TRUE )
		{
			$user = Mhi_User_Model::get_user_by_riverid($riverid->user_id);

			if ($user->loaded == TRUE)
			{
				// We found a user that's already set up in the Crowdmap system
				return $user;
			}
			else
			{
				// This user is authenticated with RiverID but doesn't have an account
				//   on the master Crowdmap database. Let's add them!

				$mhi_user_id = Mhi_User_Model::save_user(array('email'=>$riverid->email,'riverid'=>$riverid->user_id),TRUE);

				return Mhi_User_Model::get_user_by_mhi_user_id($mhi_user_id);
			}

		}

		return FALSE;
	}

	// Update user
	// $a should be an assoc array including at least one of email, firstname, lastname and password (plain text)

	static function update($id,$a)
	{
		$salt = Kohana::config('auth.salt_pattern');

		$mhi_user = ORM::factory('mhi_user',$id);

		if(isset($a['firstname']))
			$mhi_user->firstname = $a['firstname'];

		if(isset($a['lastname']))
			$mhi_user->lastname = $a['lastname'];

		if(isset($a['email']))
		{
			$email_details = array('old_email'=>$mhi_user->email,'new_email'=>$a['email']);
			Event::run('ushahidi_action.mhi_user_email_change', $email_details);

			$mhi_user->email = $a['email'];
		}

		return $mhi_user->save();
	}
}
