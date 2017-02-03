<?php
class Search extends CI_Controller {
	
	function Search() {
		parent::__construct();
		$this->load->database();
		
		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('search_model', 'search');
		$this->load->model('orders_model');
		$this->load->model('inventory_model');
		$this->load->model('collections_model');
		$this->load->library('pagination');	
		$this->load->helper('download');	
		
		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

		$this->permissions->access('can_supersearch');
		
	}

	
	#------------------------------------------------------
	# Do a nice redirect to make the url cleaner
	#------------------------------------------------------
	function index() {
		
		$keyword = $this->input->get('s');
		$keyword = str_replace('"', '', $keyword);
		$keyword = trim($keyword);
		$keyword = base64_encode(urlencode($keyword));
		redirect("search/results/$keyword");
		
	}
	
	#------------------------------------------------------
	# Display the results page
	#------------------------------------------------------
	function results() {

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();

		// Load some additional data for menus and stuff
		$data['collection_groups'] = $this->collections_model->collectionsNav();
	
		// Get the search results
		$keyword = base64_decode( urldecode($this->uri->segment(3)) );
		
		if ($this->config->item('can_access_order_listview')) {
			$data['orders'] = $this->search->orders($keyword);
		}
		
		if ($this->config->item('can_access_inventory_list')) {
			$data['inventory'] 	= $this->search->inventory($keyword);
		}
		
		if ($this->config->item('can_access_customer_list')) {
			$data['customers'] 	= $this->search->customers($keyword);
		}

		// Packing note templates
		$data['templates'] = $this->orders_model->returnTemplates();
		$data['template_types'] = $this->orders_model->returnTemplateTypes();

		// Output the required template
		$data['title']	 = 'Search';
		$data['keyword'] = urldecode($keyword);
		$data['content'] = 'global/search';
		$this->load->view('global/template', $data);
	
	}

}