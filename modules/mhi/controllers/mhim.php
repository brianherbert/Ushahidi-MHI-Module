<?php defined('SYSPATH') or die('No direct script access.');

class MHIM_Controller extends Template_Controller {

	public $template = 'layout';

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		// Load templates
		$this->template->content = new View('mhim/mhim_home');
		$this->template->header = new View('mhim/mhim_header');
		$this->template->footer = new View('mhim/mhim_footer');
	}

}
