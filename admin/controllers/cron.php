<?php
class Cron extends CI_Controller {
	
	function Cron() {
		parent::__construct();
		
		//Load what we need
		$this->load->database();
		$this->load->helper('directory');
		$this->load->library('email');
		$this->load->library('parser');

		//Load settings
		$this->load->model('settings_model');
		$this->load->model('google_model','google');
		$this->settings_model->initConfig();

	}
	
	function index() {
		
		//Output as plain text
		header('Content-type: text/plain');
		
		//Some vars to pass to the view
		$data['site_url'] = $this->config->item('site_root');
		
		//Load the view
		$this->parser->parse('feeds/crons', $data);
	}
	
	function cron_test() {
		//Use this for testing code...
	}

	#------------------------------------------------------
	# Generate google feeds for
	# - Shopping (base.xml)
	# - Sitemap (sitemap.xml)
	#------------------------------------------------------
	function googlise() {		
		$this->google->generate("", false, true);
	}

}