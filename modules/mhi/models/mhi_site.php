<?php defined('SYSPATH') or die('No direct script access.');


class mhi_site_Model extends ORM
{
	protected $table_name = 'mhi_site';

	protected $primary_key = 'id';

	protected $primary_val = 'site_domain';

	public $site_name;

	public $site_tagline;

	static function domain_exists($site_domain)
	{

		// TODO: We could also do a subdomain lookup to see if the subdomain is being used already for something other than MHI

		// Check if the subdomain has been taken

		$count = ORM::factory('mhi_site')->where('site_domain',$site_domain)->count_all();
		if ($count != 0)
			return true;

		return false;
	}

	//site_domains must be an array
	static function domain_owner($site_domains)
	{
		$return_array = array();
		// Return an array of the MHI user id of the owner of the domain

		foreach($site_domains as $domain)
		{
			$result = ORM::factory('mhi_site')->where('site_domain',$domain)->find_all();
			foreach ($result as $res){
				$return_array[$res->id] = $res->user_id;
			}
		}

		return $return_array;
	}

	// This function activates or deactivates a site

	static function activation($domain,$activation)
	{
		$result = ORM::factory('mhi_site')->where('site_domain',$domain)->find_all();

		foreach ($result as $res){
			$site = ORM::factory('mhi_site',$res->id);
			$site->site_active = $activation;
			$site->save();
		}

		return true;
	}

	// $a should be an assoc array including user_id, site_domain, site_privacy, site_active

	static function save_site($a)
	{
		$mhi_site = ORM::factory('mhi_site');
		$mhi_site->user_id = $a['user_id'];
		$mhi_site->site_domain = $a['site_domain'];
		$mhi_site->site_privacy = $a['site_privacy'];
		$mhi_site->site_active = $a['site_active'];
		$mhi_site->site_dateadd = date('Y-m-d H:i:s', time());
		$mhi_site->save();

		$result = ORM::factory('mhi_site')->where('site_domain',$a['site_domain'])->find_all();
		$id = 0;
		foreach ($result as $res)
			$id = $res->id;

		return $id;
	}

	// Get sites, user_id returns all of that users sites

	static function get_user_sites($user_id=FALSE,$detailed_data=FALSE)
	{
		$result = ORM::factory('mhi_site')->where('user_id',$user_id)->find_all();

		$sites = array();
		foreach ($result as $res)
		{
			if ($detailed_data != FALSE)
			{
				// Go to the deployment's database and grab some additional details
				$details = Mhi_Site_Model::get_site_details($res->site_domain);
				$res->site_name = $details['site_name'];
				$res->site_tagline = $details['site_tagline'];
			}
			$sites[] = $res;
		}

		return $sites;
	}

	// Get sites, user_id returns all of that users sites

	function count_user_sites($user_id=FALSE,$detailed_data=FALSE)
	{
		$count = ORM::factory('mhi_site')->where('user_id',$user_id)->count_all();

		return $count;
	}

	function get_site_details($domain)
	{
		$mhi_db = Kohana::config('database.default');
		$table_prefix = $mhi_db['table_prefix'];
		$mhi_db_name = $mhi_db['connection']['database'];

		// Switch to new DB for a moment

		$base_db = DBGenesis::current_db();

		mysql_select_db($base_db.'_'.$domain);

		$query = 'SELECT * FROM settings WHERE `key` = \'site_name\' OR `key` = \'site_tagline\'';
		$result = mysql_query($query);
		$settings = array();
		while ($row = mysql_fetch_assoc($result))
		{
			$settings[$row['key']] = $row['value'];
		}

		if ( ! isset($settings['site_name']) ){
			$settings['site_name'] = 'Unknown';
		}

		if ( ! isset($settings['site_tagline']) ){
			$settings['site_tagline'] = 'Unknown';
		}

		mysql_select_db($base_db);

		return $settings;
	}

	// Depreciated
	function get_db_versions($limit=100,$only_needs_upgrade=TRUE)
	{
		$mhi_db = Kohana::config('database.default');
		$table_prefix = $mhi_db['table_prefix'];
		$mhi_db_name = $mhi_db['connection']['database'];

		$dbs = Mhi_Site_Database_Model::get_all_db_details();

		// Switch to new DB for a moment

		$array = array();

		$settings = ORM::factory('settings', 1);
		$current_version = $settings->db_version;

		$i = 0;
		foreach($dbs as $db)
		{
			if($i >= $limit) break;

			mysql_query('USE '.$db.';');

			// START: Everything that happens in the deployment DB happens below
			$settings = ORM::factory('settings', 1);

			if($only_needs_upgrade == FALSE OR $settings->db_version != $current_version)
			{
				$array[$db] = $settings->db_version;
				$i++;
			}
		}

		// END: Everything that happens in the deployment DB happens above

		//Switch back to our db, otherwise we would be running off some other deployments DB and that wouldn't work
		mysql_query('USE '.$mhi_db_name);

		return $array;
	}
}
