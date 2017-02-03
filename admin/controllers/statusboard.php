<?php
class Statusboard extends CI_Controller {
	
	function Statusboard() {
		parent::__construct();

		$this->load->database();
		
		$this->load->helper('file');
		$this->load->model('settings_model');
		$this->load->model('reports_model');
		$this->load->library('parser');

		$this->settings_model->initConfig();

	}
	
	/* !create a setup function here - this should be index() */
	function index() {

		$this->permissions->access('can_access_options_services');
	
		$key = $this->settings_model->getAPIKey();
		
		if ($key != false) {
		
			$this_year = date('Y');
			$last_year = $this_year - 1;
			
			$url_this_year = urlencode(site_url("statusboard/graph/$this_year?key=$key"));
			$url_last_year = urlencode(site_url("statusboard/graph/$last_year?key=$key"));
		
			$data['link']  = "<p>Simply download the app from the App Store and view this page on your iPad. Then click on the links below to set them up in Status Board.</p>";
			$data['link'] .= "<p>";
			$data['link'] .= '<a href="panicboard://?url='.$url_this_year.'&panel=graph" class="button">'.$this_year.' Sales</a> ';
			$data['link'] .= '<a href="panicboard://?url='.$url_last_year.'&panel=graph" class="button">'.$last_year.' Sales</a> ';
			$data['link'] .= "</p>";
		} else {
			$data['link']  = "<p class=\"redtext\">Hold on a minute! It looks like you haven't set up any API keys yet. Set one up now...</p>";
			$data['link'] .= '<p><a href="'.site_url('options/api').'" class="button">Create API Key</a><p>';
		}
	
		//Send data to dashboard
		$data['title']	 = 'Dashboard';
		$data['content'] = 'feeds/statusboard';
		$this->load->view('global/template', $data);
	}
	
	#------------------------------------------------------
	# Display graph of this years sales
	#------------------------------------------------------
	function graph() {

		//Output plain text
		header('Content-type: text/plain; charset=utf-8');

		// Get the API key from the uri
		$api_key = $_GET['key'];
		
		// Get the year if it's in the url (segment 3)
		$year = ($this->uri->segment(3) != '') ? $this->uri->segment(3) : date('Y');
		
		// Check if key exists and do the necessary
		if ( $this->settings_model->apiExists($api_key) ) {
		
			//Chart data
			$data = $this->reports_model->statusboard($year);
			
			//Output to view
			$title = config_item('store_name') . " $year Sales";
	
			// Create the JSON
			$output = '{
				"graph" : {
					"title" : "' . $title . '",
					"refreshEveryNSeconds" : 120,
					"type": "bar",
					"datasequences" : [
						{
							"title" : "Totals",
							"datapoints" : [';
								$output .= $data;
			$output .= ']
						}
					]
				}
			}';
			
		} else {
			
			$output = "You do not have permission to view this graph.";
			
		}

		// Output the JSON or message
		echo $output;
	
	}

}