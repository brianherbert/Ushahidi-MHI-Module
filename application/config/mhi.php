<?php defined('SYSPATH') or die('No direct script access.');

	// This would be something like "crowdmap.com"
	$config['main_mhi_domain'] = 'INSERT_MHI_DOMAIN_HERE';

	/**
	 * Array of subdomains you would like to prevent MHI users from using.
	 *
	 */
	$config['blocked_subdomains'] = array();

	$config['blocked_subdomains'] = array_map('strtolower', $config['blocked_subdomains']);

	/**
	 * Array of subdomains that will trigger an "edition" homepage
	 *
	 */
	$config['edition_subdomains'] = array();
	$config['edition_subdomains'] = array_map('strtolower', $config['edition_subdomains']);
	$config['current_edition'] = '';
?>