<?php defined('SYSPATH') or die('No direct script access.');

class mhimodifyconfig {

	/**
	 * Configures config the way it needs to be configured for the magic to happen
	 */
	public function __construct()
	{
		// ROUTES
		Event::add('ushahidi_action.config_routes', array($this, 'routes'));

		// UPLOAD
		Event::add('ushahidi_action.config_upload', array($this, 'upload'));
	}

	public function routes(){
		$config = Event::$data;

		// If MHI is set and we are hitting the main site, forward to the welcome, instance signup page
		if(Kohana::config('settings.subdomain') == '') {
			$config['_default'] = 'mhi';
		}

		Event::$data = $config;

		return true;
	}

	public function upload(){
		$config = Event::$data;

		if(substr_count($_SERVER["HTTP_HOST"],'.') > 1)
		{
			$subdomain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],'.'));
			$config['directory'] = DOCROOT.'media/uploads'.'/'.$subdomain;
			$config['relative_directory'] = 'media/uploads'.'/'.$subdomain;
		}

		Event::$data = $config;

		return true;
	}

}

new mhimodifyconfig;