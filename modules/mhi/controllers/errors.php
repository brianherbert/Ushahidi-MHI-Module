<?php defined('SYSPATH') or die('No direct script access.');

class Errors_Controller extends Template_Controller {

	// MHI template

	public $template = 'layout';

	function __construct()
	{
		parent::__construct();

		$this->template->header = '';
		$this->template->footer = '';
	}

	public function index()
	{

	}

	public function error_404()
	{
		$this->template->content = new View('mhi/mhi_404');
		//$this->template->content = 'content';
		$this->template->render(TRUE);
	}

}
