<?php defined('SYSPATH') OR die('No direct access allowed.');

class mhitabs {

	/**
	 * Generate Main Tabs
     * @param string $this_body
	 * @return string $menu
     */
	public static function main_tabs($this_body = FALSE)
	{
		$menu = "";

		// Home
		$menu .= "<li><a href=\"".url::site()."mhi\" ";
		$menu .= ($this_body == 'crowdmap-home') ? " class=\"active\"" : "";
	 	$menu .= ">Home</a></li>";

		// Custom Pages
		$pages = ORM::factory('page')->where('page_active', '1')->find_all();
		foreach ($pages as $page)
		{
			$menu .= "<li><a href=\"".url::site()."mhi/page/".$page->id."\" ";
			$menu .= ($this_body == 'page_'.$page->id) ? " class=\"active\"" : "";
		 	$menu .= ">".$page->page_tab."</a></li>";
		}

		// Contact
		$menu .= "<li><a href=\"".url::site()."mhi/contact\" ";
		$menu .= ($this_body == 'crowdmap-contact') ? " class=\"active\"" : "";
	 	$menu .= ">Contact Us</a></li>";

		echo $menu;
	}
}
