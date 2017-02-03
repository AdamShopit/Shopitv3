<?php
class Image extends CI_Controller {
	
	function Image() {
		parent::__construct();
	
		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->library('image_lib');
	}
	
	function index() {
		redirect();
	}
				
	#------------------------------------------------------
	# Resize a product image
	#------------------------------------------------------
	function resize() {
		
		ini_set("memory_limit","96M");

		//Cache the image file
		/*
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 90 day")));
		*/

		$image 	= $_SERVER['DOCUMENT_ROOT'].$this->config->item('path_to_uploads').$this->uri->segment(3); //file to get from  server
		
		// Set new dimensions we want
		if ($this->uri->segment(4) != '' && $this->uri->segment(5) != ''):
			$newWidth 	= $this->uri->segment(4); //set width via url
			$newHeight 	= $this->uri->segment(5); //set height via url
		else:
			$newWidth 	= $this->config->item('image_width');  //set default thumbnail width
			$newHeight 	= $this->config->item('image_height'); //set default thumbnail height
		endif;


		$config['image_library'] 	= 'gd2';
		$config['source_image'] 	= $image;
		$config['create_thumb'] 	= FALSE;
		$config['dynamic_output'] 	= TRUE;
		$config['maintain_ratio'] 	= TRUE;
		$config['master_dim'] 		= 'auto';
		$config['width']  			= $newWidth;
		$config['height'] 			= $newHeight;
		$config['quality'] 			= $this->config->item('image_quality');
		
		$this->image_lib->initialize($config);
		
		$this->image_lib->resize();

	}

	#------------------------------------------------------
	# Create thumbnail from file
	#------------------------------------------------------
	function thumbnail() {
		
		ini_set("memory_limit","96M");

		//Cache the image file
		/*
		header("Cache-Control: private, max-age=10800, pre-check=10800");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822,strtotime(" 90 day")));
		*/
		
		$image 	= $_SERVER['DOCUMENT_ROOT'].$this->config->item('path_to_uploads').$this->uri->segment(3); //file to get from  server
		
		//echo $image;
		
		$config['image_library'] 	= 'gd2';
		$config['source_image'] 	= $image;
		$config['create_thumb'] 	= FALSE;
		$config['dynamic_output'] 	= TRUE;
		$config['maintain_ratio'] 	= TRUE;
		$config['master_dim'] 		= 'auto';
		$config['width']  			= $this->config->item('thumbnail_width');
		$config['height'] 			= $this->config->item('thumbnail_height');
		$config['quality'] 			= $this->config->item('image_quality');
		
		$this->image_lib->initialize($config);
		
		$this->image_lib->resize();
	    
	}
			
	#------------------------------------------------------
	# Resize category image to icon
	#------------------------------------------------------
	function icon() {
		
		$image 	= $_SERVER['DOCUMENT_ROOT'].'/files/images/' . $this->uri->segment(3); //file to get from server
		
		$config['image_library'] 	= 'gd2';
		$config['source_image'] 	= $image;
		$config['create_thumb'] 	= FALSE;
		$config['dynamic_output'] 	= TRUE;
		$config['maintain_ratio'] 	= TRUE;
		$config['master_dim'] 		= 'auto';
		$config['width']  			= $this->uri->segment(4);
		$config['height'] 			= $this->uri->segment(4);
		$config['quality'] 			= $this->config->item('image_quality');
		
		$this->image_lib->initialize($config);
		
		$this->image_lib->resize();

	}



}